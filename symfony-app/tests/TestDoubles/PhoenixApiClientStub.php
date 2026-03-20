<?php

declare(strict_types = 1);

namespace App\Tests\TestDoubles;

use App\Service\PhoenixService\Dto\PhotoEntryDto;
use App\Service\PhoenixService\PhoenixApiClientInterface;

class PhoenixApiClientStub implements PhoenixApiClientInterface
{
    /** @var list<PhotoEntryDto> */
    private array $photos = [];

    private ?\RuntimeException $exception = null;

    /** @param list<PhotoEntryDto> $photos */
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

        return $this->photos;
    }
}
