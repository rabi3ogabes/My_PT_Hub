<?php

namespace App\Notifications\Channels;

final class FcmMessage
{
    /**
     * @param  array<string, string>  $data
     */
    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly array $data = [],
        public readonly ?string $threadId = null,
        public readonly ?string $imageUrl = null,
    ) {}
}
