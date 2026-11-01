<?php

namespace App\Domain\Workouts\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Workouts\Jobs\ProcessExerciseVideo;
use App\Domain\Workouts\Models\Exercise;
use App\Domain\Workouts\Support\SearchText;
use App\Domain\Workouts\Support\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Exercise library: search, tenant custom exercises, favourites and demo videos. */
class ExerciseLibrary
{
    /**
     * Visible exercises (global + current tenant) filtered by the library filters.
     *
     * @param  array{q?: ?string, muscle?: ?string, equipment?: ?string, level?: ?string, favorites?: bool, pattern?: ?string, custom?: ?bool}  $filters
     * @return Builder<Exercise>
     */
    public function query(array $filters, ?User $user = null): Builder
    {
        $query = Exercise::query()->where('is_published', true);

        $term = trim((string) ($filters['q'] ?? ''));
        if ($term !== '') {
            $this->applySearch($query, $term);
        }
        if (! empty($filters['muscle'])) {
            $muscle = (string) $filters['muscle'];
            $query->where(fn (Builder $q) => $q->where('primary_muscle', $muscle)
                ->orWhere('secondary_muscles', 'like', '%"'.SearchText::escapeLike($muscle).'"%'));
        }
        if (! empty($filters['equipment'])) {
            foreach (array_filter(explode(',', (string) $filters['equipment'])) as $item) {
                $query->where('equipment', 'like', '%"'.SearchText::escapeLike(trim($item)).'"%');
            }
        }
        if (! empty($filters['level'])) {
            $query->whereIn('level', explode(',', (string) $filters['level']));
        }
        if (! empty($filters['pattern'])) {
            $query->where('pattern', $filters['pattern']);
        }
        if (isset($filters['custom']) && $filters['custom'] !== null) {
            $filters['custom'] ? $query->whereNotNull('tenant_id') : $query->whereNull('tenant_id');
        }
        if (! empty($filters['favorites']) && $user) {
            $query->whereIn('id', DB::table('exercise_favorites')->where('user_id', $user->id)->select('exercise_id'));
        }

        return $query;
    }

    /**
     * Matches the normalised multi-locale haystack, plus raw LIKE on the JSON name/aliases
     * columns (covers rows written without the model hook, e.g. bulk SQL imports).
     *
     * @param  Builder<Exercise>  $query
     */
    public function applySearch(Builder $query, string $term): void
    {
        $normalized = SearchText::normalize($term);
        $raw = mb_strtolower($term, 'UTF-8');
        $isPgsql = $query->getConnection()->getDriverName() === 'pgsql';
        $cast = fn (string $column) => $isPgsql ? "lower({$column}::text)" : "lower({$column})";

        $query->where(function (Builder $q) use ($normalized, $raw, $cast) {
            $words = array_filter(explode(' ', $normalized));
            if ($words !== []) {
                $q->where(function (Builder $w) use ($words) {
                    foreach ($words as $word) {
                        $w->whereRaw("search_text LIKE ? ESCAPE '\\'", ['%'.SearchText::escapeLike($word).'%']);
                    }
                });
            }
            $like = '%'.SearchText::escapeLike($raw).'%';
            $q->orWhereRaw($cast('name')." LIKE ? ESCAPE '\\'", [$like])
                ->orWhereRaw($cast('aliases')." LIKE ? ESCAPE '\\'", [$like]);
        });
    }

    /** @return list<string> */
    public function favoriteIds(User $user): array
    {
        return DB::table('exercise_favorites')->where('user_id', $user->id)->pluck('exercise_id')->all();
    }

    /** Toggles a favourite; returns the new state. */
    public function toggleFavorite(User $user, Exercise $exercise): bool
    {
        $exists = DB::table('exercise_favorites')->where('user_id', $user->id)->where('exercise_id', $exercise->id)->exists();
        if ($exists) {
            DB::table('exercise_favorites')->where('user_id', $user->id)->where('exercise_id', $exercise->id)->delete();

            return false;
        }
        DB::table('exercise_favorites')->insert(['user_id' => $user->id, 'exercise_id' => $exercise->id]);

        return true;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Exercise
    {
        return DB::transaction(function () use ($data) {
            $exercise = new Exercise;
            $this->fill($exercise, $data);
            $exercise->slug = $this->uniqueSlug((string) ($data['slug'] ?? $this->baseName($exercise)));
            $exercise->save();
            if (array_key_exists('alternative_ids', $data)) {
                $this->syncAlternatives($exercise, (array) $data['alternative_ids']);
            }
            if (! empty($data['video_url'])) {
                $this->attachEmbed($exercise, (string) $data['video_url']);
            }

            return $exercise->refresh();
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Exercise $exercise, array $data): Exercise
    {
        $this->assertEditable($exercise);

        return DB::transaction(function () use ($exercise, $data) {
            $this->fill($exercise, $data);
            $exercise->save();
            if (array_key_exists('alternative_ids', $data)) {
                $this->syncAlternatives($exercise, (array) $data['alternative_ids']);
            }
            if (array_key_exists('video_url', $data) && $data['video_url']) {
                $this->attachEmbed($exercise, (string) $data['video_url']);
            }

            return $exercise->refresh();
        });
    }

    public function delete(Exercise $exercise): void
    {
        $this->assertEditable($exercise);
        $exercise->delete();
    }

    /** Global library rows are read-only for tenants. */
    public function assertEditable(Exercise $exercise): void
    {
        if (! $exercise->isOwnedByCurrentTenant()) {
            throw ApiException::forbidden(__('workouts.global_exercise_read_only'));
        }
    }

    /** Stores an uploaded demo video and queues HLS processing. */
    public function uploadVideo(Exercise $exercise, UploadedFile $file): Exercise
    {
        $this->assertEditable($exercise);
        $disk = (string) config('workouts.media_disk', 'public');
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'mp4');
        $path = $file->storeAs('exercise-videos/'.$exercise->id, 'original-'.Str::lower((string) Str::ulid()).'.'.$ext, $disk);

        $exercise->forceFill([
            'video_provider' => 'upload',
            'video_path' => $path,
            'video_url' => Storage::disk($disk)->url((string) $path),
            'video_status' => 'pending',
            'video_size_bytes' => $file->getSize() ?: null,
            'poster_url' => null,
            'preview_url' => null,
        ])->save();

        ProcessExerciseVideo::dispatch($exercise->id);

        return $exercise->refresh();
    }

    /** Stores a YouTube/Vimeo link as an embed (no download needed). */
    public function attachEmbed(Exercise $exercise, string $url): Exercise
    {
        $embed = self::parseEmbed($url) ?? throw ApiException::invalid('validation_failed', __('workouts.video_url_unsupported'), ['video_url' => [__('workouts.video_url_unsupported')]]);
        $exercise->forceFill([
            'video_provider' => $embed['provider'],
            'video_url' => $embed['url'],
            'poster_url' => $embed['poster_url'],
            'preview_url' => null,
            'video_status' => 'ready',
            'video_path' => null,
            'video_size_bytes' => null,
        ])->save();

        return $exercise;
    }

    /** @return array{provider: string, url: string, poster_url: ?string}|null */
    public static function parseEmbed(string $url): ?array
    {
        $url = trim($url);
        if (preg_match('~^(?:https?://)?(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/)|youtu\.be/|youtube-nocookie\.com/embed/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return ['provider' => 'youtube', 'url' => 'https://www.youtube-nocookie.com/embed/'.$m[1], 'poster_url' => 'https://i.ytimg.com/vi/'.$m[1].'/hqdefault.jpg'];
        }
        if (preg_match('~^(?:https?://)?(?:www\.|player\.)?vimeo\.com/(?:video/|channels/[^/]+/|groups/[^/]+/videos/)?(\d{6,12})~', $url, $m)) {
            return ['provider' => 'vimeo', 'url' => 'https://player.vimeo.com/video/'.$m[1], 'poster_url' => null];
        }

        return null;
    }

    /** @param array<string, mixed> $data */
    private function fill(Exercise $exercise, array $data): void
    {
        foreach (['name', 'instructions', 'tips', 'mistakes'] as $field) {
            if (array_key_exists($field, $data)) {
                $exercise->{$field} = Translatable::merge($data[$field], $exercise->{$field});
            }
        }
        if (array_key_exists('aliases', $data)) {
            $exercise->aliases = Translatable::merge($data['aliases'], $exercise->aliases);
        }
        foreach (['primary_muscle', 'secondary_muscles', 'equipment', 'pattern', 'mechanics', 'force', 'level', 'is_unilateral', 'is_bodyweight', 'metrics', 'is_published'] as $field) {
            if (array_key_exists($field, $data)) {
                $exercise->{$field} = $data[$field];
            }
        }
        if (! $exercise->exists) {
            $exercise->level ??= 'beginner';
            $exercise->metrics ??= ($exercise->is_bodyweight ? ['reps'] : ['weight', 'reps']);
        }
    }

    /** @param list<string> $ids */
    private function syncAlternatives(Exercise $exercise, array $ids): void
    {
        $visible = Exercise::query()->whereIn('id', $ids)->where('id', '!=', $exercise->id)->pluck('id')->all();
        $exercise->alternatives()->sync($visible);
    }

    private function baseName(Exercise $exercise): string
    {
        $name = $exercise->name ?? [];

        return (string) ($name['en'] ?? (array_values(array_filter($name))[0] ?? 'exercise'));
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'exercise-'.Str::lower(Str::random(6));
        $slug = $base;
        $i = 2;
        while (Exercise::query()->withTrashed()->where('slug', $slug)->whereNotNull('tenant_id')->where('tenant_id', app(TenantContext::class)->id())->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
