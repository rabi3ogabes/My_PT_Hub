<?php

namespace App\Domain\Payments\Services;

use App\Domain\Payments\Models\Invoice;
use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Models\Refund;
use App\Domain\Payments\Support\Pdf;
use App\Domain\Shared\Support\Money;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/** Sequential per-tenant invoices and credit notes, ZATCA-ready for KSA, multilingual PDFs with Arabic RTL. */
final class InvoiceService
{
    public function __construct(private readonly TenantContext $context) {}

    /**
     * Allocates the next number for (tenant, type) under a row lock so concurrent
     * payments can never share or skip a number. Must run inside the caller's transaction.
     *
     * @return array{sequence: int, number: string}
     */
    public function nextNumber(Tenant $tenant, string $type = 'invoice'): array
    {
        return DB::transaction(function () use ($tenant, $type) {
            DB::table('invoice_sequences')->insertOrIgnore(['tenant_id' => $tenant->id, 'type' => $type, 'last_value' => 0]);
            $row = DB::table('invoice_sequences')->where('tenant_id', $tenant->id)->where('type', $type)->lockForUpdate()->first();
            $next = (int) $row->last_value + 1;
            DB::table('invoice_sequences')->where('tenant_id', $tenant->id)->where('type', $type)->update(['last_value' => $next]);

            $prefix = (string) $tenant->setting('payments.invoice_prefix.'.$type, $type === 'credit_note' ? 'CN' : 'INV');

            return ['sequence' => $next, 'number' => sprintf('%s-%s-%06d', $prefix, now()->format('Y'), $next)];
        });
    }

    /** @return array<string, mixed> */
    public function seller(Tenant $tenant): array
    {
        $legal = $tenant->legal ?? [];

        return [
            'name' => $tenant->name,
            'legal_name' => $legal['legal_name'] ?? $tenant->name,
            'vat_number' => $legal['vat_number'] ?? null,
            'registration_number' => $legal['registration_number'] ?? $legal['cr_number'] ?? null,
            'address' => $legal['address'] ?? null,
            'email' => $legal['email'] ?? null,
            'phone' => $legal['phone'] ?? null,
            'country' => $tenant->country,
            'logo_path' => $tenant->logo_path,
            'brand_color' => $tenant->brand_color,
        ];
    }

    /** Issues the (single) invoice of a paid order. Idempotent. */
    public function issueForOrder(Order $order): Invoice
    {
        $tenant = Tenant::findOrFail($order->tenant_id);

        return $this->context->run($tenant, fn () => DB::transaction(function () use ($order, $tenant) {
            $existing = Invoice::query()->where('order_id', $order->id)->where('type', 'invoice')->lockForUpdate()->first();
            if ($existing) {
                return $existing;
            }
            $order->loadMissing('items', 'client');
            $seq = $this->nextNumber($tenant, 'invoice');
            $issuedAt = $order->paid_at ?? now();
            $seller = $this->seller($tenant);

            $lines = $order->items->map(fn ($item) => [
                'description' => $item->description,
                'quantity' => (int) $item->quantity,
                'unit_amount_minor' => (int) $item->unit_amount_minor,
                'total_minor' => (int) $item->total_minor,
            ])->values()->all();
            if ($order->discount_minor > 0) {
                $lines[] = ['description' => __('invoices.discount', [], $tenant->locale), 'quantity' => 1, 'unit_amount_minor' => -$order->discount_minor, 'total_minor' => -$order->discount_minor];
            }

            return Invoice::create([
                'order_id' => $order->id,
                'client_id' => $order->client_id,
                'type' => 'invoice',
                'sequence' => $seq['sequence'],
                'number' => $seq['number'],
                'currency' => $order->currency,
                'subtotal_minor' => $order->total_minor - $order->tax_minor,
                'tax_minor' => $order->tax_minor,
                'total_minor' => $order->total_minor,
                'tax_rate' => $order->tax_rate,
                'status' => $order->isPaid() ? 'paid' : 'open',
                'due_on' => $order->isPaid() ? null : now()->addDays((int) config('payments.manual_due_days', 7))->toDateString(),
                'seller' => $seller,
                'buyer' => ['name' => $order->client?->name ?? $order->customer_name, 'email' => $order->client?->email ?? $order->customer_email, 'client_id' => $order->client_id],
                'lines' => $lines,
                'locale' => $order->client?->locale ?? $tenant->locale ?? 'en',
                'issued_at' => $issuedAt,
                'qr_tlv' => $this->zatca($tenant, $seller, $issuedAt->toIso8601String(), $order->total_minor, $order->tax_minor, $order->currency),
            ]);
        }));
    }

    /** Credit note for a refund, referencing the original invoice. Idempotent per refund. */
    public function creditNote(Order $order, Refund $refund): Invoice
    {
        $tenant = Tenant::findOrFail($order->tenant_id);

        return $this->context->run($tenant, fn () => DB::transaction(function () use ($order, $refund, $tenant) {
            $existing = Invoice::query()->where('refund_id', $refund->id)->where('type', 'credit_note')->first();
            if ($existing) {
                return $existing;
            }
            $original = $this->issueForOrder($order);
            $amount = (int) $refund->amount_minor;
            $tax = $original->total_minor > 0 ? (int) round($amount * $original->tax_minor / $original->total_minor) : 0;
            $seq = $this->nextNumber($tenant, 'credit_note');
            $issuedAt = now();

            return Invoice::create([
                'order_id' => $order->id,
                'client_id' => $order->client_id,
                'type' => 'credit_note',
                'sequence' => $seq['sequence'],
                'number' => $seq['number'],
                'currency' => $original->currency,
                'subtotal_minor' => $amount - $tax,
                'tax_minor' => $tax,
                'total_minor' => $amount,
                'tax_rate' => $original->tax_rate,
                'status' => 'paid',
                'seller' => $original->seller,
                'buyer' => $original->buyer,
                'lines' => [[
                    'description' => __('invoices.credit_note_line', ['number' => $original->number], $original->locale),
                    'quantity' => 1,
                    'unit_amount_minor' => $amount,
                    'total_minor' => $amount,
                ]],
                'locale' => $original->locale,
                'issued_at' => $issuedAt,
                'credit_note_for_id' => $original->id,
                'refund_id' => $refund->id,
                'qr_tlv' => $this->zatca($tenant, $original->seller, $issuedAt->toIso8601String(), $amount, $tax, $original->currency),
            ]);
        }));
    }

    /** @param array<string, mixed> $seller */
    private function zatca(Tenant $tenant, array $seller, string $timestamp, int $total, int $tax, string $currency): ?string
    {
        if (strtoupper((string) $tenant->country) !== 'SA') {
            return null;
        }
        $fmt = fn (int $minor) => number_format(Money::toMajor($minor, $currency), Money::decimals($currency), '.', '');

        return ZatcaQr::encode((string) ($seller['legal_name'] ?? $tenant->name), (string) ($seller['vat_number'] ?? ''), $timestamp, $fmt($total), $fmt($tax));
    }

    public function pdf(Invoice $invoice, ?string $locale = null): string
    {
        $locale ??= $invoice->locale ?: 'en';
        $previous = app()->getLocale();
        app()->setLocale($locale);
        try {
            $qr = null;
            if ($invoice->qr_tlv) {
                $qr = (new \chillerlan\QRCode\QRCode(new \chillerlan\QRCode\QROptions([
                    'outputType' => extension_loaded('gd') ? \chillerlan\QRCode\Output\QROutputInterface::GDIMAGE_PNG : \chillerlan\QRCode\Output\QROutputInterface::MARKUP_SVG,
                    'outputBase64' => true,
                    'scale' => 4,
                ])))->render($invoice->qr_tlv);
            }
            $logo = null;
            $logoPath = $invoice->seller['logo_path'] ?? null;
            if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                $logo = 'data:image/png;base64,'.base64_encode((string) Storage::disk('public')->get($logoPath));
            }
            $html = view('payments.pdf.invoice', [
                'invoice' => $invoice,
                'original' => $invoice->credit_note_for_id ? Invoice::withoutGlobalScopes()->find($invoice->credit_note_for_id) : null,
                'rtl' => $locale === 'ar',
                'locale' => $locale,
                'qr' => $qr,
                'logo' => $logo,
                'money' => fn (int $minor) => Money::format($minor, $invoice->currency, $locale),
            ])->render();

            return Pdf::render($html, $locale === 'ar', $invoice->number);
        } finally {
            app()->setLocale($previous);
        }
    }
}
