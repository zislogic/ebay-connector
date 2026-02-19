<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

final class AccountDeletionController extends Controller
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private readonly array $config,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        if ($request->isMethod('GET')) {
            return $this->handleChallenge($request);
        }

        return $this->handleNotification($request);
    }

    private function handleChallenge(Request $request): JsonResponse
    {
        $challengeCode = $request->input('challenge_code');

        if (! is_string($challengeCode) || $challengeCode === '') {
            return response()->json(['error' => 'Missing challenge_code'], 400);
        }

        $verificationToken = (string) ($this->config['deletion_notification']['verification_token'] ?? '');
        $endpointUrl = (string) ($this->config['deletion_notification']['endpoint_url'] ?? '');

        $challengeResponse = hash('sha256', $challengeCode . $verificationToken . $endpointUrl);

        return response()->json([
            'challengeResponse' => $challengeResponse,
        ]);
    }

    private function handleNotification(Request $request): JsonResponse
    {
        Log::info('eBay account deletion notification received', [
            'notification_id' => $request->input('notificationId'),
            'metadata' => $request->input('metadata'),
            'timestamp' => now()->toIso8601String(),
        ]);

        return response()->json(['ack' => 'success']);
    }
}
