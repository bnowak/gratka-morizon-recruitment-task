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

    public function testSaveTokenRequiresLogin(): void
    {
        $this->client->request('POST', '/profile/save-token', ['phoenix_token' => 'abc']);
        $this->assertResponseRedirects('/');
    }

    public function testSaveTokenPersistsValue(): void
    {
        $this->logIn();

        $this->client->request('POST', '/profile/save-token', ['phoenix_token' => 'my-secret-token']);

        $this->assertResponseRedirects('/profile');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.flash-message.success', 'Phoenix API token saved successfully.');
        $this->assertSelectorExists('input[name="phoenix_token"][value="my-secret-token"]');
    }

    public function testSaveTokenCanBeCleared(): void
    {
        $this->logIn();

        $this->client->request('POST', '/profile/save-token', ['phoenix_token' => 'initial-token']);
        $this->client->followRedirect();

        $this->client->request('POST', '/profile/save-token', ['phoenix_token' => '']);
        $this->assertResponseRedirects('/profile');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.flash-message.success', 'Phoenix API token saved successfully.');
    }
}
