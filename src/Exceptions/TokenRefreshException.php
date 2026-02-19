<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Exceptions;

use Exception;

final class TokenRefreshException extends Exception
{
    public static function credentialNotFound(int $credentialId): self
    {
        return new self("eBay credential with ID {$credentialId} not found");
    }

    public static function credentialInactive(int $credentialId): self
    {
        return new self("eBay credential with ID {$credentialId} is inactive");
    }

    public static function refreshFailed(int $credentialId, string $reason = ''): self
    {
        return new self("Failed to refresh token for credential {$credentialId}: {$reason}");
    }

    public static function refreshTokenExpired(int $credentialId): self
    {
        return new self("Refresh token expired for credential {$credentialId}");
    }
}
