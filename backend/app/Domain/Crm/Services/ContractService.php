<?php

namespace App\Domain\Crm\Services;

use App\Domain\Crm\Models\Contract;
use App\Domain\Crm\Models\ContractTemplate;
use App\Domain\Identity\Models\User;
use App\Domain\Payments\Support\Notify;
use App\Domain\Payments\Support\Pdf;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\Storage;

/** Contracts, waivers and PAR-Q: variable rendering, e-signature with audit trail (IP, user agent, timestamp, SHA-256). */
final class ContractService
{
    /** @return array<string, string> */
    public function variables(Tenant $tenant, User $client, ?User $coach = null): array
    {
        return [
            '{client_name}' => $client->name,
            '{client_email}' => (string) $client->email,
            '{business_name}' => $tenant->name,
            '{coach_name}' => (string) $coach?->name,
            '{date}' => now($tenant->timezone ?: 'UTC')->toDateString(),
        ];
    }

    public function render(string $body, Tenant $tenant, User $client, ?User $coach = null): string
    {
        return strtr($body, $this->variables($tenant, $client, $coach));
    }

    public function send(Tenant $tenant, User $client, User $coach, ?ContractTemplate $template, ?string $title, ?string $body): Contract
    {
        if (! $client->isClientIn($tenant->id)) {
            throw ApiException::invalid('validation_failed', __('crm_module.errors.not_a_client'), ['client_id' => [__('crm_module.errors.not_a_client')]]);
        }
        $source = $body ?? $template?->body ?? throw ApiException::invalid('validation_failed', __('crm_module.errors.body_required'), ['body' => [__('crm_module.errors.body_required')]]);

        $contract = Contract::create([
            'contract_template_id' => $template?->id,
            'client_id' => $client->id,
            'title' => $title ?: ($template?->name ?? __('contracts.default_title')),
            'rendered_body' => $this->render($source, $tenant, $client, $coach),
            'status' => 'sent',
        ]);
        ActivityLogger::log('contracts.sent', $contract, tenantId: $tenant->id);
        Notify::user($client, 'contract_sent', ['title' => $contract->title, 'business' => $tenant->name], '/contracts');

        return $contract;
    }

    /** SHA-256 over the exact rendered body followed by the signature payload. */
    public static function hash(string $renderedBody, string $signatureData): string
    {
        return hash('sha256', $renderedBody.$signatureData);
    }

    public function sign(Contract $contract, User $client, string $type, string $signature, ?string $ip, ?string $userAgent): Contract
    {
        if ($contract->client_id !== $client->id) {
            throw ApiException::forbidden();
        }
        if ($contract->status !== 'sent') {
            throw ApiException::conflict('contract_not_signable', __('contracts.errors.not_signable'));
        }
        if ($type === 'draw' && ! preg_match('#^data:image/(png|jpeg|svg\+xml);base64,[A-Za-z0-9+/=]+$#', $signature)) {
            throw ApiException::invalid('validation_failed', __('contracts.errors.invalid_signature'), ['signature_data' => [__('contracts.errors.invalid_signature')]]);
        }

        $contract->forceFill([
            'status' => 'signed',
            'signature_type' => $type,
            'signature_data' => $signature,
            'signed_ip' => $ip,
            'signed_user_agent' => $userAgent ? mb_substr($userAgent, 0, 255) : null,
            'signed_at' => now(),
            'document_hash' => self::hash($contract->rendered_body, $signature),
        ])->save();

        $path = 'contracts/'.$contract->tenant_id.'/'.$contract->id.'.pdf';
        Storage::disk('local')->put($path, $this->pdf($contract));
        $contract->forceFill(['pdf_path' => $path])->save();

        ActivityLogger::log('contracts.signed', $contract, ['hash' => $contract->document_hash, 'ip' => $ip], causerId: $client->id, tenantId: $contract->tenant_id);
        if ($tenant = Tenant::find($contract->tenant_id)) {
            Notify::staff($tenant, 'contract_signed', ['client' => $client->name, 'title' => $contract->title]);
        }

        return $contract->refresh();
    }

    public function pdf(Contract $contract, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $contract->loadMissing('client');
        $tenant = Tenant::find($contract->tenant_id);
        $html = view('payments.pdf.contract', [
            'contract' => $contract,
            'tenant' => $tenant,
            'rtl' => $locale === 'ar' || (bool) preg_match('/\p{Arabic}/u', $contract->rendered_body),
            'locale' => $locale,
        ])->render();

        return Pdf::render($html, $locale === 'ar', $contract->title);
    }
}
