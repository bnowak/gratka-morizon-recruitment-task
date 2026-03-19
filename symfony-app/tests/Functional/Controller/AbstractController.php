<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\AuthToken;
use App\Entity\Photo;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractController extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    protected function createUser(string $username = 'testuser', string $email = 'testuser@example.com'): User
    {
        $user = (new User())
            ->setUsername($username)
            ->setEmail($email);

        $authToken = (new AuthToken())
            ->setToken('test-token-' . uniqid())
            ->setUser($user);

        $this->em->persist($user);
        $this->em->persist($authToken);
        $this->em->flush();

        return $user;
    }

    protected function logIn(?User $user = null): User
    {
        if ($user === null) {
            $user = $this->createUser();
        }

        $authToken = $this->em->getRepository(AuthToken::class)->findOneBy(['user' => $user]);

        $this->client->request('GET', '/auth/' . $user->getUsername() . '/' . $authToken->getToken());
        $this->client->followRedirect();

        return $user;
    }

    protected function createPhoto(User $owner): Photo
    {
        $managedOwner = $this->em->find(User::class, $owner->getId());

        $photo = (new Photo())
            ->setImageUrl('https://example.com/photo.jpg')
            ->setUser($managedOwner);

        $this->em->persist($photo);
        $this->em->flush();

        return $photo;
    }
}
