<?php

namespace App\Http\Api\V1\Resources\Workouts;

use App\Domain\Workouts\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

/** @mixin Exercise */
class ExerciseResource extends JsonResource
{
    private bool $detailed = false;

    private bool $withTranslations = false;

    public function detailed(bool $withTranslations = false): static
    {
        $this->detailed = true;
        $this->withTranslations = $withTranslations;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var Exercise $e */
        $e = $this->resource;
        $data = [
            'id' => $e->id,
            'slug' => $e->slug,
            'name' => $e->translate('name'),
            'primary_muscle' => $e->primary_muscle,
            'secondary_muscles' => $e->secondary_muscles ?? [],
            'equipment' => $e->equipment ?? [],
            'pattern' => $e->pattern,
            'mechanics' => $e->mechanics,
            'force' => $e->force,
            'level' => $e->level,
            'is_unilateral' => (bool) $e->is_unilateral,
            'is_bodyweight' => (bool) $e->is_bodyweight,
            'metrics' => $e->metrics ?? [],
            'video' => [
                'provider' => $e->video_provider,
                'url' => $e->video_url,
                'poster_url' => $e->poster_url,
                'preview_url' => $e->preview_url,
                'status' => $e->video_status,
            ],
            'is_custom' => $e->tenant_id !== null,
            'is_favorite' => in_array($e->id, self::favoriteIds($request), true),
        ];

        if ($this->detailed) {
            $data['instructions'] = self::steps($e->translateRaw('instructions'));
            $data['tips'] = self::steps($e->translateRaw('tips'));
            $data['mistakes'] = self::steps($e->translateRaw('mistakes'));
            $data['alternatives'] = $e->relationLoaded('alternatives')
                ? $e->alternatives->map(fn (Exercise $a) => ['id' => $a->id, 'name' => $a->translate('name')])->values()->all()
                : [];
            $data['progression_parent_id'] = $e->progression_parent_id;
            $data['progression_step'] = $e->progression_step;
            if ($this->withTranslations) {
                $data['name_translations'] = (object) ($e->name ?? []);
                $data['aliases'] = (object) ($e->aliases ?? []);
                $data['instructions_translations'] = (object) ($e->instructions ?? []);
                $data['tips_translations'] = (object) ($e->tips ?? []);
                $data['mistakes_translations'] = (object) ($e->mistakes ?? []);
            }
        }

        return $data;
    }

    /** @return list<string> */
    private static function steps(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        return is_array($value) ? array_values(array_filter($value, 'is_string')) : [(string) $value];
    }

    /** Favourite exercise ids of the current user, loaded once per request. @return list<string> */
    public static function favoriteIds(Request $request): array
    {
        if (! $request->attributes->has('exercise_favorite_ids')) {
            $user = $request->user();
            $request->attributes->set('exercise_favorite_ids', $user
                ? DB::table('exercise_favorites')->where('user_id', $user->getAuthIdentifier())->pluck('exercise_id')->all()
                : []);
        }

        return $request->attributes->get('exercise_favorite_ids');
    }
}
