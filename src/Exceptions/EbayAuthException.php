<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Exceptions;

use Exception;

final class EbayAuthException extends Exception
{
    public static function invalidResponse(string $message = ''): self
    {
        return new self('Invalid OAuth response from eBay: ' . $message);
    }

    public static function tokenExchangeFailed(string $message = ''): self
    {
        return new self('Failed to exchange authorization code for tokens: ' . $message);
    }

    public static function missingConfiguration(string $key): self
    {
        return new self("Missing eBay configuration: {$key}");
    }

    public static function httpError(int $statusCode, string $message = ''): self
    {
        return new self("eBay API returned HTTP {$statusCode}: {$message}");
    }
}
