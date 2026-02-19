<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zislogic\Ebay\Connector\Auth\TokenResponse;

final class TokenResponseTest extends TestCase
{
    #[Test]
    public function it_creates_from_array(): void
    {
        $data = [
            'access_token' => 'test-token',
            'expires_in' => 7200,
            'refresh_token' => 'refresh-token',
            'refresh_token_expires_in' => 47304000,
            'token_type' => 'Bearer',
        ];

        $response = TokenResponse::fromArray($data);

        $this->assertSame('test-token', $response->accessToken);
        $this->assertSame(7200, $response->expiresIn);
        $this->assertSame('refresh-token', $response->refreshToken);
        $this->assertSame(47304000, $response->refreshTokenExpiresIn);
        $this->assertSame('Bearer', $response->tokenType);
    }

    #[Test]
    public function it_handles_missing_optional_fields(): void
    {
        $data = [
            'access_token' => 'test-token',
            'expires_in' => 7200,
        ];

        $response = TokenResponse::fromArray($data);

        $this->assertSame('test-token', $response->accessToken);
        $this->assertSame(7200, $response->expiresIn);
        $this->assertNull($response->refreshToken);
        $this->assertNull($response->refreshTokenExpiresIn);
        $this->assertNull($response->tokenType);
    }

    #[Test]
    public function it_handles_empty_array(): void
    {
        $response = TokenResponse::fromArray([]);

        $this->assertSame('', $response->accessToken);
        $this->assertSame(0, $response->expiresIn);
        $this->assertNull($response->refreshToken);
        $this->assertNull($response->refreshTokenExpiresIn);
        $this->assertNull($response->tokenType);
    }
}
