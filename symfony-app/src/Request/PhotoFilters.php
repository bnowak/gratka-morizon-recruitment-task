<?php

declare(strict_types=1);

namespace App\Request;

final class PhotoFilters
{
    public function __construct(
        public ?string $location = null,
        public ?string $camera = null,
        public ?string $description = null,
        public ?string $username = null,
        public ?\DateTimeImmutable $takenAtFrom = null,
        public ?\DateTimeImmutable $takenAtTo = null,
    ) {}
}
