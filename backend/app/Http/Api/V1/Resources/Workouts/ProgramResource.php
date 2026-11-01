<?php

namespace App\Http\Api\V1\Resources\Workouts;

use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\ProgramDay;
use App\Domain\Workouts\Models\ProgramWeek;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Program */
class ProgramResource extends JsonResource
{
    private bool $withTranslations = false;

    public function withTranslations(bool $on = true): static
    {
        $this->withTranslations = $on;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var Program $p */
        $p = $this->resource;
        $weeks = $p->relationLoaded('weeks') ? $p->weeks : null;

        $data = [
            'id' => $p->id,
            'name' => $p->translate('name'),
            'description' => $p->translate('description'),
            'goal' => $p->goal,
            'level' => $p->level,
            'days_per_week' => (int) $p->days_per_week,
            'equipment' => $p->equipment ?? [],
            'tags' => $p->tags ?? [],
            'visibility' => $p->visibility,
            'is_template' => (bool) $p->is_template,
            'is_global' => $p->tenant_id === null,
            'cover_url' => $p->cover_url,
            'weeks_count' => $weeks !== null ? $weeks->count() : (int) ($p->weeks_count ?? 0),
            'weeks' => $weeks !== null ? $weeks->map(fn (ProgramWeek $w) => [
                'id' => $w->id,
                'number' => (int) $w->number,
                'phase' => $w->phase,
                'is_deload' => (bool) $w->is_deload,
                'notes' => $w->notes,
                'days' => $w->relationLoaded('days') ? $w->days->map(fn (ProgramDay $d) => [
                    'id' => $d->id,
                    'day_number' => (int) $d->day_number,
                    'title' => $d->title,
                    'is_rest' => (bool) $d->is_rest,
                    'workout' => $d->workout_id && $d->workout ? [
                        'id' => $d->workout->id,
                        'name' => $d->workout->name,
                        'estimated_minutes' => (int) $d->workout->estimated_minutes,
                    ] : null,
                    'meal_plan_id' => $d->meal_plan_id,
                    'habit_ids' => $d->habit_ids ?? [],
                ])->values()->all() : [],
            ])->values()->all() : [],
        ];
        if ($this->withTranslations) {
            $data['name_translations'] = (object) ($p->name ?? []);
            $data['description_translations'] = (object) ($p->description ?? []);
        }

        return $data;
    }
}
