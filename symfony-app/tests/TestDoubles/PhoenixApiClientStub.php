<?php

declare(strict_types=1);

namespace App\Tests\TestDoubles;

use App\Service\Dto\PhotoEntryDto;
use App\Service\PhoenixApiClientInterface;

class PhoenixApiClientStub implements PhoenixApiClientInterface
{
    /** @var array<array{id: int, photo_url: string}> */
    private array $photos = [];

    private ?\RuntimeException $exception = null;

    /** @param array<array{id: int, photo_url: string}> $photos */
    public function setPhotos(array $photos): void
    {
        $this->photos = $photos;
    }

    public function setThrowException(?\RuntimeException $exception): void
    {
        $this->exception = $exception;
    }

    public function fetchPhotos(string $token): array
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }

        return array_map(
            static fn (array $photo) => new PhotoEntryDto(
                $photo['id'],
                $photo['photo_url'],
                $photo['camera'] ?? null,
                $photo['location'] ?? null,
                $photo['description'] ?? null,
                isset($photo['taken_at']) ? new \DateTimeImmutable($photo['taken_at']) : null,
            ),
            $this->photos,
        );
    }
}
