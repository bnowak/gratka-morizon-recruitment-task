<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Photo;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Photo>
 */
class PhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    public function findAllWithUsers(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return int[] */
    public function findExistingPhoenixPhotoIds(User $user): array
    {
        return array_column(
            $this->createQueryBuilder('p')
                ->select('p.phoenixPhotoId')
                ->where('p.user = :user')
                ->andWhere('p.phoenixPhotoId IS NOT NULL')
                ->setParameter('user', $user)
                ->getQuery()
                ->getArrayResult(),
            'phoenixPhotoId'
        );
    }

    /** @param Photo[] $photos */
    public function saveAll(array $photos): void
    {
        $em = $this->getEntityManager();
        foreach ($photos as $photo) {
            $em->persist($photo);
        }
        $em->flush();
    }
}
