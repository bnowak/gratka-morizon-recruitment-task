<?php

declare(strict_types = 1);

namespace App\Service\PhoenixService;

use App\Service\PhoenixService\Dto\PhotoEntryDto;

interface PhoenixApiClientInterface
{
    /**
     * @return list<PhotoEntryDto>
     */
    public function fetchPhotos(string $token): array;
}
