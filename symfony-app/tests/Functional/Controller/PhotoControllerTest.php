<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Likes\Like;

class PhotoControllerTest extends AbstractController
{
    public function testLikePhoto(): void
    {
        $user = $this->logIn();
        $photo = $this->createPhoto($user);

        $this->client->request('POST', '/photo/' . $photo->getId() . '/like');

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.flash-message.success', 'Photo liked!');
    }

    public function testUnlikePhoto(): void
    {
        $user = $this->logIn();
        $photo = $this->createPhoto($user);
        $photo->setLikeCounter(1);

        $like = (new Like())
            ->setUser($this->em->find(User::class, $user->getId()))
            ->setPhoto($photo);

        $this->em->persist($like);
        $this->em->flush();

        $this->client->request('POST', '/photo/' . $photo->getId() . '/like');

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.flash-message.info', 'Photo unliked!');
    }
}
