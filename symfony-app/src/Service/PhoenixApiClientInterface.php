<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Dto\PhotoEntryDto;

interface PhoenixApiClientInterface
{
    /**
     * @return list<PhotoEntryDto>
     */
    public function fetchPhotos(string $token): array;
}
