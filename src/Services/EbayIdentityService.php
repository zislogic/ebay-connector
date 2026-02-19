<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Services;

use Illuminate\Support\Facades\Http;
use Zislogic\Ebay\Connector\Exceptions\EbayAuthException;

final class EbayIdentityService
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private readonly array $config,
        private readonly string $environment,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getUser(string $accessToken): array
    {
        $baseUrl = (string) $this->config['urls'][$this->environment]['apiz'];
        $url = $baseUrl . '/commerce/identity/v1/user/';

        $response = Http::withToken($accessToken)->get($url);

        if ($response->failed()) {
            throw EbayAuthException::httpError(
                $response->status(),
                'Failed to fetch user identity from eBay',
            );
        }

        /** @var array<string, mixed> $data */
        $data = $response->json();

        if (! isset($data['userId']) || ! is_string($data['userId'])) {
            throw EbayAuthException::invalidResponse('Missing userId in identity response');
        }

        return $data;
    }
}
