<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Photo;

use App\Repository\PhotoRepository;
use App\Tests\Functional\Controller\AbstractController;
use App\Tests\TestDoubles\PhoenixApiClientStub;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ImportFromPhoenixTest extends AbstractController
{
    public function testImportRequiresLogin(): void
    {
        $this->client->request('POST', '/photo/import-from-phoenix');
        $this->assertResponseRedirects('/');
    }

    public function testImportWithoutTokenRedirectsToProfile(): void
    {
        $this->logIn();

        $this->client->request('POST', '/photo/import-from-phoenix');

        $this->assertResponseRedirects('/profile');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.flash-message.warning', 'No Phoenix API token set. Please save your token first.');
    }

    public function testImportCreatesNewPhotos(): void
    {
        $user = $this->createUser('importer', 'importer@example.com');
        $user->setPhoenixToken('valid-token');
        $this->em->flush();

        $this->logIn($user);

        static::getContainer()->get(PhoenixApiClientStub::class)->setPhotos([
            ['id' => 1, 'photo_url' => 'https://example.com/photo1.jpg'],
            ['id' => 2, 'photo_url' => 'https://example.com/photo2.jpg'],
        ]);

        $this->client->request('POST', '/photo/import-from-phoenix');

        $this->assertResponseRedirects('/profile');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.flash-message.success', 'Imported 2 new photo(s) from Phoenix.');

        $photoRepository = static::getContainer()->get(PhotoRepository::class);
        $importedIds = $photoRepository->findExistingPhoenixPhotoIds($user);
        $this->assertCount(2, $importedIds);
        $this->assertContains(1, $importedIds);
        $this->assertContains(2, $importedIds);
    }

    public function testImportSkipsDuplicates(): void
    {
        $user = $this->createUser('deduper', 'deduper@example.com');
        $user->setPhoenixToken('valid-token');
        $this->em->flush();

        $this->logIn($user);

        static::getContainer()->get(PhoenixApiClientStub::class)->setPhotos([
            ['id' => 10, 'photo_url' => 'https://example.com/photo10.jpg'],
            ['id' => 11, 'photo_url' => 'https://example.com/photo11.jpg'],
        ]);

        // First import
        $this->client->request('POST', '/photo/import-from-phoenix');
        $this->assertResponseRedirects('/profile');
        $this->client->followRedirect();

        // Second import — same photos
        $this->client->request('POST', '/photo/import-from-phoenix');
        $this->assertResponseRedirects('/profile');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.flash-message.success', 'No new photos to import.');

        $photoRepository = static::getContainer()->get(PhotoRepository::class);
        $this->assertCount(2, $photoRepository->findExistingPhoenixPhotoIds($user));
    }

    public function testImportWithUnauthorizedTokenShowsError(): void
    {
        $user = $this->createUser('unauthorized', 'unauthorized@example.com');
        $user->setPhoenixToken('wrong-token');
        $this->em->flush();

        $this->logIn($user);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getInfo')->willReturnMap([
            ['http_code', 401],
            ['url', 'http://phoenix/api/photos'],
            ['response_headers', []],
        ]);
        $clientException = new ClientException($response);

        static::getContainer()->get(PhoenixApiClientStub::class)
            ->setThrowException(new \RuntimeException('Unauthorized', 0, $clientException));

        $this->client->request('POST', '/photo/import-from-phoenix');

        $this->assertResponseRedirects('/profile');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.flash-message.error', 'Unauthorized API token. Save correct user token and try again.');
    }

    public function testImportHandlesPhoenixApiError(): void
    {
        $user = $this->createUser('erroruser', 'erroruser@example.com');
        $user->setPhoenixToken('bad-token');
        $this->em->flush();

        $this->logIn($user);

        static::getContainer()->get(PhoenixApiClientStub::class)
            ->setThrowException(new \RuntimeException());

        $this->client->request('POST', '/photo/import-from-phoenix');

        $this->assertResponseRedirects('/profile');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.flash-message.error', 'Could not connect to Phoenix API. Please try again later.');
    }
}
