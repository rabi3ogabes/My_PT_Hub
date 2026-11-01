<?php

namespace App\Http\Api\V1\Resources\Habits;

use App\Http\Api\V1\Support\ViewerContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Habits\Models\Habit */
class HabitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->translate('name'),
            'icon' => $this->icon,
            'type' => $this->type,
            'target' => $this->target !== null ? (float) $this->target : null,
            'unit' => $this->unit,
            'is_custom' => $this->tenant_id !== null,
        ];
        if (ViewerContext::isStaff($request)) {
            $data['name_translations'] = (object) ($this->name ?? []);
            $data['health_metric'] = $this->health_metric;
        }

        return $data;
    }
}
