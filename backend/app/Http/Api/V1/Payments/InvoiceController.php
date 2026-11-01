<?php

namespace App\Http\Api\V1\Payments;

use App\Domain\Payments\Models\Invoice;
use App\Domain\Payments\Services\InvoiceService;
use App\Domain\Payments\Support\Permission;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Payments\InvoiceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class InvoiceController extends ApiController
{
    public function __construct(private readonly InvoiceService $invoices) {}

    public function index(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'payments.view', 'payments.manage');
        $query = Invoice::query()->with('client')
            ->when($request->input('filter.type'), fn ($q, $t) => $q->where('type', $t))
            ->when($request->input('filter.status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('filter.client_id'), fn ($q, $id) => $q->where('client_id', $id))
            ->when($request->input('q'), fn ($q, $term) => $q->where('number', 'like', "%{$term}%"))
            ->orderByDesc('issued_at')->orderByDesc('sequence');

        return $this->paginated($query->paginate($this->perPage()), InvoiceResource::class);
    }

    public function pdf(Request $request, Invoice $invoice): Response
    {
        Permission::assert($this->user(), 'payments.view', 'payments.manage');
        $locale = $request->validate(['locale' => ['nullable', Rule::in(['en', 'ar', 'fr', 'es'])]])['locale'] ?? null;

        return self::pdfResponse($this->invoices->pdf($invoice, $locale), $invoice->number);
    }

    public static function pdfResponse(string $pdf, string $name): Response
    {
        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.preg_replace('/[^A-Za-z0-9_-]/', '_', $name).'.pdf"',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
