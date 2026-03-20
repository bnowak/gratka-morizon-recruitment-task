<?php

declare(strict_types = 1);

namespace App\Tests\Functional\Controller;

use App\Entity\AuthToken;
use App\Entity\Photo;
use App\Entity\User;
use App\Repository\AuthTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractController extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;
    protected AuthTokenRepository $authTokenRepository;
    protected RouterInterface $router;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->authTokenRepository = static::getContainer()->get(AuthTokenRepository::class);
        $this->router = static::getContainer()->get('router');
    }

    protected function request(
        string $routeName,
        array $routeParams = [],
        array $requestParams = [],
    ): void {
        $route = $this->router->getRouteCollection()->get($routeName);
        $methods = $route?->getMethods() ?? [];
        $method = $methods[0] ?? 'GET';
        $url = $this->router->generate($routeName, $routeParams);

        $this->client->request($method, $url, $requestParams);
    }

    protected function createUser(string $username = 'testuser', string $email = 'testuser@example.com'): User
    {
        $user = (new User())
            ->setUsername($username)
            ->setEmail($email);

        $authToken = (new AuthToken())
            ->setToken(bin2hex(random_bytes(16)))
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

        $authToken = $this->authTokenRepository->findOneBy(['user' => $user]);

        $this->request('auth_login', ['username' => $user->getUsername(), 'token' => $authToken->getToken()]);
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
