<?php

namespace App\Http\Api\V1\Resources\Payments;

use App\Domain\Payments\Models\Invoice;
use App\Domain\Payments\Support\MoneyJson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Invoice */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $m = fn (int $minor) => MoneyJson::make($minor, $this->currency);

        return [
            'id' => $this->id,
            'number' => $this->number,
            'type' => $this->type,
            'status' => $this->status,
            'currency' => $this->currency,
            'subtotal' => $m((int) $this->subtotal_minor),
            'tax' => $m((int) $this->tax_minor),
            'total' => $m((int) $this->total_minor),
            'tax_rate' => (float) $this->tax_rate,
            'order_id' => $this->order_id,
            'client' => $this->relationLoaded('client') && $this->client ? ['id' => $this->client->id, 'name' => $this->client->name] : null,
            'buyer' => $this->buyer,
            'seller' => collect($this->seller ?? [])->except(['logo_path'])->all(),
            'lines' => $this->lines ?? [],
            'credit_note_for_id' => $this->credit_note_for_id,
            'zatca_qr' => $this->qr_tlv,
            'locale' => $this->locale,
            'issued_at' => $this->issued_at?->toIso8601String(),
            'due_on' => $this->due_on?->toDateString(),
            'pdf_url' => $this->pdfUrl(),
        ];
    }

    protected function pdfUrl(): string
    {
        return url('api/v1/invoices/'.$this->id.'/pdf');
    }
}
