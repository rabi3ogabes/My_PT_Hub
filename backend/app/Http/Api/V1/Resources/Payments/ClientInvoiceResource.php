<?php

namespace App\Http\Api\V1\Resources\Payments;

use App\Domain\Payments\Models\Invoice;

/** @mixin Invoice */
class ClientInvoiceResource extends InvoiceResource
{
    protected function pdfUrl(): string
    {
        return url('api/v1/me/invoices/'.$this->id.'/pdf');
    }
}
