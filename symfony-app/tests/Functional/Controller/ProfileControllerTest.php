<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

class ProfileControllerTest extends AbstractController
{
    public function testProfilePage(): void
    {
        $user = $this->createUser('janedoe', 'jane@example.com');
        $user->setName('Jane')
            ->setLastName('Doe')
            ->setAge(28)
            ->setBio('Photography enthusiast.');
        $this->em->flush();

        $this->logIn($user);

        $this->client->request('GET', '/profile');

        $content = $this->client->getResponse()->getContent();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.profile-handle', '@janedoe');
        $this->assertSelectorTextContains('.profile-username', 'Jane Doe');
        $this->assertSelectorTextContains('.profile-field-value', 'jane@example.com');
        $this->assertStringContainsString('28 years old', $content);
        $this->assertSelectorTextContains('.profile-bio-text', 'Photography enthusiast.');
    }
}
