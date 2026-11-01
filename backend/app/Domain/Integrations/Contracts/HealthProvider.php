<?php

namespace App\Domain\Integrations\Contracts;

use App\Domain\Integrations\Data\SampleData;
use App\Domain\Integrations\Data\WebhookNotification;
use App\Domain\Integrations\Models\Integration;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Wearable / health data source driver. */
interface HealthProvider extends OAuthProvider
{
    /**
     * Historical import after connecting. Providers that backfill
     * asynchronously (push) request it and return an empty list.
     *
     * @return list<SampleData>
     */
    public function backfill(Integration $integration, int $days): array;

    /** @return list<SampleData> */
    public function pull(Integration $integration, CarbonInterface $since): array;

    /**
     * Verifies the webhook signature/token and parses the payload.
     *
     * @return list<WebhookNotification>
     *
     * @throws \App\Domain\Integrations\Exceptions\InvalidWebhookSignature
     */
    public function handleWebhook(Request $request): array;

    /** Answers a provider's subscription verification (GET challenge); null when unsupported. */
    public function verifyWebhookEndpoint(Request $request): ?Response;

    /**
     * Resolves a notification that only references data into samples.
     *
     * @return list<SampleData>
     */
    public function fetchNotification(Integration $integration, WebhookNotification $notification): array;
}
