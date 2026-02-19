<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Auth;

final readonly class TokenResponse
{
    public function __construct(
        public string $accessToken,
        public int $expiresIn,
        public ?string $refreshToken = null,
        public ?int $refreshTokenExpiresIn = null,
        public ?string $tokenType = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            accessToken: (string) ($data['access_token'] ?? ''),
            expiresIn: (int) ($data['expires_in'] ?? 0),
            refreshToken: isset($data['refresh_token']) ? (string) $data['refresh_token'] : null,
            refreshTokenExpiresIn: isset($data['refresh_token_expires_in']) ? (int) $data['refresh_token_expires_in'] : null,
            tokenType: isset($data['token_type']) ? (string) $data['token_type'] : null,
        );
    }
}
