<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Photo;
use App\Entity\User;
use App\Request\PhotoFilters;
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

    /** @return Photo[] */
    public function findAllWithUsers(?PhotoFilters $filters = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->orderBy('p.id', 'ASC');

        if ($filters?->location !== null) {
            $qb->andWhere('p.location LIKE :location')
               ->setParameter('location', '%' . $filters->location . '%');
        }
        if ($filters?->camera !== null) {
            $qb->andWhere('p.camera LIKE :camera')
               ->setParameter('camera', '%' . $filters->camera . '%');
        }
        if ($filters?->description !== null) {
            $qb->andWhere('p.description LIKE :description')
               ->setParameter('description', '%' . $filters->description . '%');
        }
        if ($filters?->username !== null) {
            $qb->andWhere('u.username LIKE :username')
               ->setParameter('username', '%' . $filters->username . '%');
        }
        if ($filters?->takenAtFrom !== null) {
            $qb->andWhere('p.takenAt >= :takenAtFrom')
               ->setParameter('takenAtFrom', $filters->takenAtFrom);
        }
        if ($filters?->takenAtTo !== null) {
            $qb->andWhere('p.takenAt <= :takenAtTo')
               ->setParameter('takenAtTo', $filters->takenAtTo);
        }

        return $qb->getQuery()->getResult();
    }

    /** @return int[] */
    public function findExistingPhoenixPhotoIds(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.phoenixPhotoId')
            ->where('p.user = :user')
            ->andWhere('p.phoenixPhotoId IS NOT NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleColumnResult();
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
