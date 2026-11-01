<?php

namespace App\Http\Api\V1\Resources\Payments;

/** @mixin \App\Domain\Payments\Models\Invoice */
class ClientInvoiceResource extends InvoiceResource
{
    protected function pdfUrl(): string
    {
        return url('api/v1/me/invoices/'.$this->id.'/pdf');
    }
}
