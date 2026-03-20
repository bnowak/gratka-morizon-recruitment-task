<?php

declare(strict_types = 1);

namespace App\Service\PhoenixService;

use App\Service\PhoenixService\Dto\PhotoEntryDto;
use App\Service\PhoenixService\Exception\PhoenixApiException;
use App\Service\PhoenixService\Exception\PhoenixRateLimitException;
use App\Service\PhoenixService\Exception\PhoenixUnauthorizedException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
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
                static fn (array $photo) => new PhotoEntryDto(
                    $photo['id'],
                    $photo['photo_url'],
                    $photo['camera'] ?? null,
                    $photo['location'] ?? null,
                    $photo['description'] ?? null,
                    isset($photo['taken_at']) ? new \DateTimeImmutable($photo['taken_at']) : null,
                ),
                $response->toArray()['photos'] ?? [],
            );
        } catch (HttpExceptionInterface $e) {
            $statusCode = $e->getResponse()->getStatusCode();

            throw match ($statusCode) {
                401 => new PhoenixUnauthorizedException('Phoenix API returned 401 Unauthorized', previous: $e),
                429 => new PhoenixRateLimitException('Phoenix API returned 429 Too Many Requests', previous: $e),
                default => new PhoenixApiException(
                    'Phoenix API returned unexpected HTTP error: ' . $statusCode,
                    previous: $e,
                ),
            };
        } catch (ExceptionInterface $e) {
            throw new PhoenixApiException('Failed to connect to Phoenix API: ' . $e->getMessage(), previous: $e);
        }
    }
}
