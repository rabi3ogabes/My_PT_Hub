<?php

use App\Domain\Payments\Models\Invoice;
use App\Domain\Payments\Services\InvoiceService;
use App\Domain\Payments\Services\PaymentService;
use App\Domain\Payments\Services\ZatcaQr;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Payments\Support\BuildsPayments;

uses(BuildsPayments::class);

beforeEach(fn () => Notification::fake());

it('numbers invoices sequentially per tenant and per type', function () {
    [$a] = $this->coachIn('QA', 'QAR');
    [$b] = $this->coachIn('QA', 'QAR');
    $svc = app(InvoiceService::class);

    $numbers = collect(range(1, 25))->map(fn () => $svc->nextNumber($a, 'invoice')['sequence'])->all();
    expect($numbers)->toBe(range(1, 25))
        ->and($svc->nextNumber($b, 'invoice')['sequence'])->toBe(1)
        ->and($svc->nextNumber($a, 'credit_note')['sequence'])->toBe(1)
        ->and($svc->nextNumber($a, 'invoice')['number'])->toBe('INV-'.now()->format('Y').'-000026');
});

it('issues exactly one invoice per paid order with gap-free numbers', function () {
    [$tenant] = $this->coachIn('QA', 'QAR');
    $manual = $this->connectGateway($tenant, 'manual');
    [$product, $price] = $this->makeProduct($tenant);

    $orders = collect(range(1, 5))->map(fn ($i) => $this->makeOrder($tenant, $product, $price, ['email' => "c{$i}@example.com", 'name' => "Client {$i}"]));
    foreach ($orders as $order) {
        app(PaymentService::class)->markPaid($order, ['driver' => 'manual', 'method' => 'cash']);
        app(PaymentService::class)->markPaid($order, ['driver' => 'manual', 'method' => 'cash']);
        app(InvoiceService::class)->issueForOrder($order->refresh());
    }

    $sequences = Invoice::withoutGlobalScopes()->where('tenant_id', $tenant->id)->orderBy('sequence')->pluck('sequence')->all();
    expect($sequences)->toBe([1, 2, 3, 4, 5])
        ->and(Invoice::withoutGlobalScopes()->distinct()->count('number'))->toBe(5);
    unset($manual);
});

it('adds ZATCA-ready seller VAT details and a TLV QR for Saudi invoices', function () {
    [$tenant] = $this->coachIn('SA', 'SAR');
    $tenant->forceFill(['legal' => ['legal_name' => 'مؤسسة سارة للتدريب', 'vat_number' => '310122393500003', 'address' => 'Riyadh']])->save();
    $this->connectGateway($tenant, 'manual');
    [$product, $price] = $this->makeProduct($tenant, [], ['amount_minor' => 11500]);
    $order = $this->makeOrder($tenant, $product, $price);
    app(PaymentService::class)->markPaid($order, ['driver' => 'manual', 'method' => 'cash']);

    $invoice = Invoice::withoutGlobalScopes()->where('order_id', $order->id)->firstOrFail();
    expect($invoice->seller['vat_number'])->toBe('310122393500003')
        ->and($invoice->tax_minor)->toBe(1500)->and($invoice->subtotal_minor)->toBe(10000)->and((float) $invoice->tax_rate)->toBe(15.0);
    $tlv = ZatcaQr::decode($invoice->qr_tlv);
    expect($tlv[1])->toBe('مؤسسة سارة للتدريب')->and($tlv[2])->toBe('310122393500003')->and($tlv[4])->toBe('115.00')->and($tlv[5])->toBe('15.00');
});

it('renders invoice PDFs in English and Arabic (RTL) for staff and the owning client', function () {
    [$tenant, $owner] = $this->coachIn('AE', 'AED');
    $client = $this->addClient($tenant, $owner);
    $other = $this->addClient($tenant, $owner);
    $this->connectGateway($tenant, 'manual');
    [$product, $price] = $this->makeProduct($tenant);
    $order = $this->makeOrder($tenant, $product, $price, ['client' => $client]);
    app(PaymentService::class)->markPaid($order, ['driver' => 'manual', 'method' => 'cash']);
    $invoice = Invoice::withoutGlobalScopes()->where('order_id', $order->id)->firstOrFail();

    $en = $this->actingAsIn($owner, $tenant)->get("/api/v1/invoices/{$invoice->id}/pdf")->assertOk()->assertHeader('Content-Type', 'application/pdf');
    expect(substr($en->getContent(), 0, 4))->toBe('%PDF');
    $ar = $this->actingAsIn($owner, $tenant)->get("/api/v1/invoices/{$invoice->id}/pdf?locale=ar")->assertOk();
    expect(substr($ar->getContent(), 0, 4))->toBe('%PDF');

    $this->actingAsIn($owner, $tenant)->getJson('/api/v1/invoices')->assertOk()->assertJsonPath('data.0.number', $invoice->number)->assertJsonPath('data.0.total.amount_minor', 39900);

    $this->actingAsIn($client, $tenant)->getJson('/api/v1/me/invoices')->assertOk()->assertJsonPath('data.0.id', $invoice->id)
        ->assertJsonPath('data.0.pdf_url', url("api/v1/me/invoices/{$invoice->id}/pdf"));
    $this->actingAsIn($client, $tenant)->get("/api/v1/me/invoices/{$invoice->id}/pdf")->assertOk()->assertHeader('Content-Type', 'application/pdf');
    $this->actingAsIn($other, $tenant)->getJson("/api/v1/me/invoices/{$invoice->id}/pdf")->assertNotFound();
});
