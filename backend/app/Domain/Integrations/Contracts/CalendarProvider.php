<?php

namespace App\Domain\Integrations\Contracts;

use App\Domain\Calendar\Models\Event;
use App\Domain\Integrations\Data\BusyBlock;
use App\Domain\Integrations\Models\Integration;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Two-way calendar driver (Google Calendar, Microsoft Graph). */
interface CalendarProvider extends OAuthProvider
{
    /** @return list<BusyBlock> busy intervals from the provider's free/busy API */
    public function busyBlocks(Integration $integration, CarbonInterface $from, CarbonInterface $to): array;

    /** Creates or updates the external copy of a platform event; returns the external id. */
    public function upsertEvent(Integration $integration, Event $event, ?string $externalId = null): string;

    public function deleteEvent(Integration $integration, string $externalId): void;

    /**
     * Starts a push channel/subscription for calendar changes.
     *
     * @return array<string, mixed> settings to persist on the integration (channel ids, expiry)
     */
    public function watch(Integration $integration, string $callbackUrl): array;

    public function stopWatch(Integration $integration): void;

    /** Handles validation handshakes (Graph validationToken); null when not a handshake. */
    public function verifyWebhookEndpoint(Request $request): ?Response;

    /**
     * Verifies a change notification and returns the ids of the affected integrations.
     *
     * @return list<string>
     *
     * @throws \App\Domain\Integrations\Exceptions\InvalidWebhookSignature
     */
    public function handleWebhook(Request $request): array;
}
