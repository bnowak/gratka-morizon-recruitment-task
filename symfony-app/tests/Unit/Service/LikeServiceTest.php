<?php

declare(strict_types = 1);

namespace App\Tests\Unit\Service;

use App\Entity\Photo;
use App\Entity\User;
use App\Repository\LikeRepository;
use App\Service\LikeService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class LikeServiceTest extends TestCase
{
    private LikeRepository $likeRepository;
    private EntityManagerInterface $em;
    private LikeService $likeService;

    protected function setUp(): void
    {
        $this->likeRepository = $this->createMock(LikeRepository::class);
        /**
         * deprecated workaround for mocking "virtual" wrapInTransaction method
         * which is defined on EntityManagerInterface using phpdoc
         */
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)
            ->addMethods(['wrapInTransaction'])
            ->getMockForAbstractClass();
        $this->em->method('wrapInTransaction')
            ->willReturnCallback(static fn(callable $cb) => $cb());

        $this->likeService = new LikeService($this->likeRepository, $this->em);
    }

    public function testLike(): void
    {
        $photo = new Photo();
        $user = new User();

        $this->em->expects($this->once())->method('wrapInTransaction');
        $this->likeRepository->expects($this->once())->method('createLike')->with($photo, $user);
        $this->likeRepository->expects($this->once())->method('updatePhotoCounter')->with($photo, 1);

        $this->likeService->like($photo, $user);
    }

    public function testUnlike(): void
    {
        $photo = new Photo();
        $user = new User();

        $this->em->expects($this->once())->method('wrapInTransaction');
        $this->likeRepository->expects($this->once())->method('removeLike')->with($photo, $user);
        $this->likeRepository->expects($this->once())->method('updatePhotoCounter')->with($photo, -1);

        $this->likeService->unlike($photo, $user);
    }
}
