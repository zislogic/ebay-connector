<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Zislogic\Ebay\Connector\Tests\TestCase;

final class AccountDeletionTest extends TestCase
{
    #[Test]
    public function it_handles_challenge_verification(): void
    {
        $response = $this->get('/ebay/account-deletion?challenge_code=test123');

        $response->assertOk();
        $response->assertJsonStructure(['challengeResponse']);

        $data = $response->json();
        $expected = hash('sha256', 'test123' . 'test-token' . 'http://localhost/ebay/account-deletion');

        $this->assertSame($expected, $data['challengeResponse']);
    }

    #[Test]
    public function it_rejects_missing_challenge_code(): void
    {
        $response = $this->get('/ebay/account-deletion');

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Missing challenge_code']);
    }

    #[Test]
    public function it_handles_deletion_notification(): void
    {
        $response = $this->postJson('/ebay/account-deletion', [
            'notificationId' => '12345',
            'metadata' => [
                'userId' => 'ebay-user-123',
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['ack' => 'success']);
    }
}
