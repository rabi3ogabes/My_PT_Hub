<?php

namespace App\Http\Api\V1\Resources\Messaging;

use App\Domain\Messaging\Models\Automation;
use App\Domain\Messaging\Models\AutomationRun;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Automation */
class AutomationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'trigger' => $this->trigger,
            'trigger_label' => __('automations.triggers.'.$this->trigger),
            'trigger_config' => (object) ($this->trigger_config ?? []),
            'conditions' => $this->conditions ?? [],
            'actions' => $this->actions ?? [],
            'is_active' => (bool) $this->is_active,
            'runs_count' => $this->runs_count !== null ? (int) $this->runs_count : null,
            'recent_runs' => $this->relationLoaded('runs') ? $this->runs->map(fn (AutomationRun $r) => [
                'id' => $r->id,
                'client_id' => $r->client_id,
                'status' => $r->status,
                'result' => $r->result,
                'created_at' => $r->created_at?->toIso8601String(),
            ])->values()->all() : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
