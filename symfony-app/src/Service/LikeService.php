<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Photo;
use App\Entity\User;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;

class LikeService
{
    public function __construct(
        private LikeRepository $likeRepository,
        private EntityManagerInterface $em,
    ) {}

    public function like(Photo $photo, User $user): void
    {
        $this->em->wrapInTransaction(function () use ($photo, $user): void {
            $this->likeRepository->createLike($photo, $user);
            $this->likeRepository->updatePhotoCounter($photo, 1);
        });
    }

    public function unlike(Photo $photo, User $user): void
    {
        $this->em->wrapInTransaction(function () use ($photo, $user): void {
            $this->likeRepository->removeLike($photo, $user);
            $this->likeRepository->updatePhotoCounter($photo, -1);
        });
    }

    public function hasUserLikedPhoto(Photo $photo, User $user): bool
    {
        return $this->likeRepository->hasUserLikedPhoto($photo, $user);
    }
}
