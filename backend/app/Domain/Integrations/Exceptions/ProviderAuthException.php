<?php

namespace App\Domain\Integrations\Exceptions;

/** The user's grant is no longer valid (revoked, expired refresh token). Requires reconnect. */
class ProviderAuthException extends ProviderException {}
