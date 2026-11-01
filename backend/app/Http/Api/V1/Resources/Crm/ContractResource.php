<?php

namespace App\Http\Api\V1\Resources\Crm;

use App\Domain\Crm\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Contract */
class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'kind' => $this->relationLoaded('template') ? $this->template?->kind : null,
            'template_id' => $this->contract_template_id,
            'client' => $this->relationLoaded('client') && $this->client ? ['id' => $this->client->id, 'name' => $this->client->name] : null,
            'body' => $this->rendered_body,
            'status' => $this->status,
            'signature_type' => $this->signature_type,
            'signed_at' => $this->signed_at?->toIso8601String(),
            'signed_ip' => $this->signed_ip,
            'document_hash' => $this->document_hash,
            'pdf_url' => $request->user()?->isStaffIn($this->tenant_id) ? url('api/v1/contracts/'.$this->id.'/pdf') : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
