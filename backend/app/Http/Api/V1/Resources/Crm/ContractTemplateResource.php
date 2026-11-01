<?php

namespace App\Http\Api\V1\Resources\Crm;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Crm\Models\ContractTemplate */
class ContractTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'kind' => $this->kind,
            'body' => $this->body,
            'variables' => ['{client_name}', '{client_email}', '{business_name}', '{coach_name}', '{date}'],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
