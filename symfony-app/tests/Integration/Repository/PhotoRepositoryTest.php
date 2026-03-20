<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Repository;

use App\Entity\AuthToken;
use App\Entity\Photo;
use App\Entity\User;
use App\Repository\PhotoRepository;
use App\Request\PhotoFilters;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PhotoRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private PhotoRepository $repo;

    /** @var array<string, int> */
    private array $photoIds;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->repo = static::getContainer()->get(PhotoRepository::class);

        $alice = (new User())->setUsername('alice')->setEmail('alice@example.com');
        $aliceToken = (new AuthToken())->setToken('token-alice')->setUser($alice);
        $this->em->persist($alice);
        $this->em->persist($aliceToken);

        $bob = (new User())->setUsername('bob')->setEmail('bob@example.com');
        $bobToken = (new AuthToken())->setToken('token-bob')->setUser($bob);
        $this->em->persist($bob);
        $this->em->persist($bobToken);

        $p1 = (new Photo())
            ->setImageUrl('https://example.com/1.jpg')
            ->setUser($alice)
            ->setLocation('Paris')
            ->setCamera('Canon EOS')
            ->setDescription('Sunset over the ocean')
            ->setTakenAt(new \DateTimeImmutable('2023-06-15 12:00:00'));

        $p2 = (new Photo())
            ->setImageUrl('https://example.com/2.jpg')
            ->setUser($alice)
            ->setLocation('Berlin')
            ->setCamera('Nikon Z6')
            ->setDescription('Mountain view')
            ->setTakenAt(new \DateTimeImmutable('2024-01-10 12:00:00'));

        $p3 = (new Photo())
            ->setImageUrl('https://example.com/3.jpg')
            ->setUser($bob)
            ->setLocation('Tokyo')
            ->setCamera('Canon EOS')
            ->setDescription('City lights')
            ->setTakenAt(new \DateTimeImmutable('2023-09-20 12:00:00'));

        $this->em->persist($p1);
        $this->em->persist($p2);
        $this->em->persist($p3);
        $this->em->flush();

        $this->photoIds = [
            'p1' => $p1->getId(),
            'p2' => $p2->getId(),
            'p3' => $p3->getId(),
        ];
    }

    public static function findAllWithUsersProvider(): \Generator
    {
        yield 'no filters' => [
            null,
            ['p1', 'p2', 'p3'],
        ];
        yield 'location Paris' => [
            new PhotoFilters(location: 'Paris'),
            ['p1'],
        ];
        yield 'camera Nikon' => [
            new PhotoFilters(camera: 'Nikon'),
            ['p2'],
        ];
        yield 'camera Canon (multi-match)' => [
            new PhotoFilters(camera: 'Canon'),
            ['p1', 'p3'],
        ];
        yield 'description ocean' => [
            new PhotoFilters(description: 'ocean'),
            ['p1'],
        ];
        yield 'username alice' => [
            new PhotoFilters(username: 'alice'),
            ['p1', 'p2'],
        ];
        yield 'username bob' => [
            new PhotoFilters(username: 'bob'),
            ['p3'],
        ];
        yield 'takenAt range 2023' => [
            new PhotoFilters(
                takenAtFrom: new \DateTimeImmutable('2023-01-01'),
                takenAtTo: new \DateTimeImmutable('2023-12-31 23:59:59'),
            ),
            ['p1', 'p3'],
        ];
        yield 'mixed location+camera' => [
            new PhotoFilters(location: 'Paris', camera: 'Canon'),
            ['p1'],
        ];
        yield 'mixed username+takenAt range' => [
            new PhotoFilters(
                username: 'alice',
                takenAtFrom: new \DateTimeImmutable('2023-01-01'),
                takenAtTo: new \DateTimeImmutable('2023-12-31 23:59:59'),
            ),
            ['p1'],
        ];
    }

    #[DataProvider('findAllWithUsersProvider')]
    public function testFindAllWithUsers(?PhotoFilters $filters, array $expectedKeys): void
    {
        $expectedIds = array_map(fn(string $key) => $this->photoIds[$key], $expectedKeys);

        $results = $this->repo->findAllWithUsers($filters);
        $ids = array_map(fn(Photo $p) => $p->getId(), $results);

        $this->assertSame($expectedIds, $ids);
    }
}
