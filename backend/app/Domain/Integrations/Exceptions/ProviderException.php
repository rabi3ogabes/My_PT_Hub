<?php

namespace App\Domain\Integrations\Exceptions;

use RuntimeException;

/** A provider call failed (network, 5xx, unexpected payload). Retried with backoff. */
class ProviderException extends RuntimeException {}
