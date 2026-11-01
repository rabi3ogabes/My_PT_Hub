<?php

namespace App\Domain\Workouts\Services;

use App\Domain\Shared\Support\ApiException;
use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\ProgramDay;
use App\Domain\Workouts\Models\ProgramWeek;
use App\Domain\Workouts\Models\Workout;
use App\Domain\Workouts\Support\Translatable;
use Illuminate\Support\Facades\DB;

/**
 * Programs → weeks → days. Week/day rows are diffed in place (never delete +
 * re-insert) because logged sessions reference program_day ids.
 */
class ProgramBuilder
{
    public const VISIBILITIES = ['private', 'tenant_template', 'public'];

    public const LEVELS = ['beginner', 'intermediate', 'advanced'];

    public const PHASES = ['hypertrophy', 'strength', 'peak', 'deload', 'base', 'power', 'endurance', 'technique', 'conditioning'];

    public const WITH = ['weeks.days.workout'];

    /** @param array<string, mixed> $data */
    public function create(array $data, ?string $createdBy = null, bool $global = false): Program
    {
        return DB::transaction(function () use ($data, $createdBy, $global) {
            $program = new Program;
            if ($global) {
                $program->tenant_id = null;
            }
            $program->created_by = $createdBy;
            $this->fill($program, $data);
            $program->save();
            $this->syncWeeks($program, $data['weeks'] ?? [], $global);

            return $program->load(self::WITH);
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Program $program, array $data): Program
    {
        $this->assertEditable($program);

        return DB::transaction(function () use ($program, $data) {
            $this->fill($program, $data);
            $program->save();
            if (array_key_exists('weeks', $data)) {
                $this->syncWeeks($program, $data['weeks'] ?? []);
            }

            return $program->unsetRelation('weeks')->load(self::WITH);
        });
    }

    public function assertEditable(Program $program): void
    {
        if (! $program->isOwnedByCurrentTenant()) {
            throw ApiException::forbidden(__('workouts.global_program_read_only'));
        }
    }

    /**
     * Copies a program (e.g. a public template) into the current tenant. Workouts owned by
     * another tenant are never referenced; global workouts stay shared, tenant workouts are kept.
     */
    public function duplicate(Program $source, ?string $createdBy = null): Program
    {
        $source->loadMissing(self::WITH);
        $payload = $this->toPayload($source);
        $payload['name'] = collect($source->name ?? [])->map(fn ($n, $locale) => __('workouts.copy_of', ['name' => $n], (string) $locale))->all();
        $payload['visibility'] = 'private';

        $copy = $this->create($payload, $createdBy);
        $copy->forceFill(['slug' => null])->save();

        return $copy;
    }

    /** Copies week $week into $toNumber (replacing it) or appends it as a new last week. */
    public function copyWeek(Program $program, ProgramWeek $week, ?int $toNumber = null): Program
    {
        $this->assertEditable($program);
        if ($week->program_id !== $program->id) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException;
        }

        return DB::transaction(function () use ($program, $week, $toNumber) {
            $week->loadMissing('days');
            $number = $toNumber ?? ((int) ProgramWeek::query()->where('program_id', $program->id)->max('number') + 1);
            $target = ProgramWeek::query()->where('program_id', $program->id)->where('number', $number)->first();
            if ($target && $target->id === $week->id) {
                return $program->load(self::WITH);
            }
            $target ??= new ProgramWeek(['program_id' => $program->id, 'number' => $number]);
            $target->fill(['phase' => $week->phase, 'is_deload' => $week->is_deload, 'notes' => $week->notes])->save();

            $this->syncDays($target, $week->days->map(fn (ProgramDay $d) => [
                'day_number' => $d->day_number,
                'title' => $d->title,
                'is_rest' => $d->is_rest,
                'workout_id' => $d->workout_id,
                'meal_plan_id' => $d->meal_plan_id,
                'habit_ids' => $d->habit_ids,
            ])->all());

            return $program->unsetRelation('weeks')->load(self::WITH);
        });
    }

    /** @return array<string, mixed> */
    public function toPayload(Program $program): array
    {
        $program->loadMissing(self::WITH);

        return [
            'name' => $program->name,
            'description' => $program->description,
            'goal' => $program->goal,
            'level' => $program->level,
            'days_per_week' => $program->days_per_week,
            'equipment' => $program->equipment ?? [],
            'tags' => $program->tags ?? [],
            'visibility' => $program->visibility,
            'is_template' => $program->is_template,
            'cover_url' => $program->cover_url,
            'weeks' => $program->weeks->map(fn (ProgramWeek $w) => [
                'number' => $w->number,
                'phase' => $w->phase,
                'is_deload' => $w->is_deload,
                'notes' => $w->notes,
                'days' => $w->days->map(fn (ProgramDay $d) => [
                    'day_number' => $d->day_number,
                    'title' => $d->title,
                    'is_rest' => $d->is_rest,
                    'workout_id' => $d->workout_id,
                    'meal_plan_id' => $d->meal_plan_id,
                    'habit_ids' => $d->habit_ids ?? [],
                ])->all(),
            ])->all(),
        ];
    }

    /** @param array<string, mixed> $data */
    private function fill(Program $program, array $data): void
    {
        foreach (['name', 'description'] as $field) {
            if (array_key_exists($field, $data)) {
                $program->{$field} = Translatable::merge($data[$field], $program->{$field});
            }
        }
        foreach (['slug', 'goal', 'level', 'days_per_week', 'equipment', 'tags', 'visibility', 'is_template', 'cover_url'] as $field) {
            if (array_key_exists($field, $data)) {
                $program->{$field} = $data[$field];
            }
        }
        $program->level ??= 'intermediate';
        $program->visibility ??= 'private';
    }

    /** @param list<array<string, mixed>> $weeks */
    private function syncWeeks(Program $program, array $weeks, bool $global = false): void
    {
        $this->assertWorkoutsVisible($weeks, $global);

        $existing = ProgramWeek::query()->where('program_id', $program->id)->get()->keyBy('id');
        $keep = [];
        // Park current numbers out of the way of the unique (program_id, number) index.
        ProgramWeek::query()->where('program_id', $program->id)->update(['number' => DB::raw('number + 10000')]);

        foreach (array_values($weeks) as $i => $data) {
            $id = $data['id'] ?? null;
            $week = is_string($id) && $existing->has($id) ? $existing->get($id) : new ProgramWeek(['program_id' => $program->id]);
            $week->fill([
                'number' => $i + 1,
                'phase' => $data['phase'] ?? null,
                'is_deload' => (bool) ($data['is_deload'] ?? false),
                'notes' => $data['notes'] ?? null,
            ])->save();
            $keep[] = $week->id;
            $this->syncDays($week, $data['days'] ?? []);
        }

        ProgramWeek::query()->where('program_id', $program->id)->whereNotIn('id', $keep)->delete();
    }

    /** @param list<array<string, mixed>> $days */
    private function syncDays(ProgramWeek $week, array $days): void
    {
        $existing = ProgramDay::query()->where('program_week_id', $week->id)->get()->keyBy('id');
        $byNumber = $existing->keyBy('day_number');
        $keep = [];
        foreach (array_values($days) as $data) {
            $id = $data['id'] ?? null;
            $number = (int) $data['day_number'];
            $day = (is_string($id) && $existing->has($id)) ? $existing->get($id)
                : ($byNumber->has($number) && ! in_array($byNumber->get($number)->id, $keep, true) ? $byNumber->get($number) : new ProgramDay(['program_week_id' => $week->id]));
            $workoutId = $data['workout_id'] ?? null;
            $day->fill([
                'day_number' => $number,
                'title' => $data['title'] ?? null,
                'is_rest' => (bool) ($data['is_rest'] ?? false) && ! $workoutId,
                'workout_id' => $workoutId,
                'meal_plan_id' => $data['meal_plan_id'] ?? null,
                'habit_ids' => array_values($data['habit_ids'] ?? []),
            ])->save();
            $keep[] = $day->id;
        }
        ProgramDay::query()->where('program_week_id', $week->id)->whereNotIn('id', $keep)->delete();
    }

    /** @param list<array<string, mixed>> $weeks */
    private function assertWorkoutsVisible(array $weeks, bool $global): void
    {
        $ids = collect($weeks)->flatMap(fn ($w) => collect($w['days'] ?? [])->pluck('workout_id'))->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return;
        }
        $query = Workout::query()->whereIn('id', $ids);
        if ($global) {
            $query->withoutGlobalScopes()->whereNull('tenant_id')->whereNull('deleted_at');
        }
        $missing = $ids->diff($query->pluck('id'));
        if ($missing->isNotEmpty()) {
            throw ApiException::invalid('validation_failed', __('api.validation_failed'), ['weeks' => [__('workouts.workout_not_found')]]);
        }
    }
}
