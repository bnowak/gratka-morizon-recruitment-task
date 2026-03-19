<?php

namespace App\Service\Dto;

final class PhotoEntryDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $photoUrl,
        public readonly ?string $camera,
        public readonly ?string $location,
        public readonly ?string $description,
        public readonly ?\DateTimeImmutable $takenAt,
    )
    {
    }
}