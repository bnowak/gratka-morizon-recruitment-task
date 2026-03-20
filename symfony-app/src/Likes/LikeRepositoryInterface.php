<?php
declare(strict_types=1);

namespace App\Likes;

use App\Entity\Photo;
use App\Entity\User;

interface LikeRepositoryInterface
{
    /** Does not flush. */
    public function removeLike(Photo $photo, User $user): void;

    public function hasUserLikedPhoto(Photo $photo, User $user): bool;

    /** Does not flush. */
    public function createLike(Photo $photo, User $user): Like;

    /** Does not flush. */
    public function updatePhotoCounter(Photo $photo, int $increment): void;

    /** @return int[] */
    public function getLikedPhotoIds(User $user): array;
}
