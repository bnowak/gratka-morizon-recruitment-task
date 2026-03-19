<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Dto\PhotoEntryDto;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PhoenixApiClient implements PhoenixApiClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $phoenixBaseUrl,
    ) {
    }

    public function fetchPhotos(string $token): array
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                $this->phoenixBaseUrl . '/api/photos',
                [
                    'headers' => ['access-token' => $token],
                ],
            );

            /**
             * In bigger projects it's recommended to use Symfony serializer here
             */
            return array_map(
                static fn (array $photo) => new PhotoEntryDto($photo['id'], $photo['photo_url']),
                $response->toArray()['photos'] ?? [],
            );
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('Failed to fetch photos from Phoenix API', previous: $e);
        }
    }
}
