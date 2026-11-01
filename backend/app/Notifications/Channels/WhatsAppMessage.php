<?php

namespace App\Notifications\Channels;

/** An approved WhatsApp template to send through WhatsAppChannel. */
final class WhatsAppMessage
{
    /**
     * @param  array<string, scalar|null>  $params  named body parameters (see config integrations.whatsapp.templates)
     */
    public function __construct(
        public readonly string $template,
        public readonly array $params = [],
        public readonly ?string $locale = null,
    ) {}
}
