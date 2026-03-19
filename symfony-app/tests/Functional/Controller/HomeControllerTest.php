<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

class HomeControllerTest extends AbstractController
{
    public function testHomeAsGuest(): void
    {
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('.profile-menu');
    }

    public function testHomeAsLoggedInUser(): void
    {
        $this->logIn();

        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.profile-menu');
        $this->assertSelectorExists('.profile-icon');
    }
}
