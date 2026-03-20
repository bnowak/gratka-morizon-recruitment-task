<?php

declare(strict_types=1);

namespace App\Likes;

use App\Entity\Photo;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class LikeRepository extends ServiceEntityRepository implements LikeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Like::class);
    }

    #[\Override]
    public function removeLike(Photo $photo, User $user): void
    {
        $em = $this->getEntityManager();

        $like = $em->createQueryBuilder()
            ->select('l')
            ->from(Like::class, 'l')
            ->where('l.user = :user')
            ->andWhere('l.photo = :photo')
            ->setParameter('user', $user)
            ->setParameter('photo', $photo)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($like) {
            $em->remove($like);
        }
    }

    #[\Override]
    public function createLike(Photo $photo, User $user): Like
    {
        $like = new Like();
        $like->setUser($user);
        $like->setPhoto($photo);

        $em = $this->getEntityManager();
        $em->persist($like);

        return $like;
    }

    #[\Override]
    public function hasUserLikedPhoto(Photo $photo, User $user): bool
    {
        $likes = $this->createQueryBuilder('l')
            ->select('l.id')
            ->where('l.user = :user')
            ->andWhere('l.photo = :photo')
            ->setParameter('user', $user)
            ->setParameter('photo', $photo)
            ->getQuery()
            ->getArrayResult();

        return count($likes) > 0;
    }

    #[\Override]
    public function getLikedPhotoIds(User $user): array
    {
        return $this->createQueryBuilder('l')
            ->select('IDENTITY(l.photo) AS photo_id')
            ->where('l.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleColumnResult();
    }

    #[\Override]
    public function updatePhotoCounter(Photo $photo, int $increment): void
    {
        $em = $this->getEntityManager();
        $photo->setLikeCounter($photo->getLikeCounter() + $increment);
        $em->persist($photo);
    }
}
