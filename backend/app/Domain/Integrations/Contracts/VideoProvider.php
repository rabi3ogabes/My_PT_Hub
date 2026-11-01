<?php

namespace App\Domain\Integrations\Contracts;

use App\Domain\Calendar\Models\Event;
use App\Domain\Integrations\Models\Integration;

interface VideoProvider extends OAuthProvider
{
    /**
     * Creates a meeting for the event.
     *
     * @return array{id: string, join_url: string, start_url: string|null}
     */
    public function createMeeting(Integration $integration, Event $event): array;
}
