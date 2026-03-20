<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\User;
use App\Likes\LikeRepository;
use App\Likes\LikeService;
use App\Repository\PhotoRepository;
use App\Service\Dto\PhotoEntryDto;
use App\Service\PhoenixApiClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PhotoController extends AbstractController
{
    #[Route('/photo/{id}/like', name: 'photo_like')]
    public function like(
        $id,
        LikeRepository $likeRepository,
        LikeService $likeService,
        PhotoRepository $photoRepository,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $photo = $photoRepository->find($id);

        if (!$photo) {
            throw $this->createNotFoundException('Photo not found');
        }

        $likeRepository->setUser($user);

        if ($likeRepository->hasUserLikedPhoto($photo)) {
            $likeRepository->unlikePhoto($photo);
            $this->addFlash('info', 'Photo unliked!');
        } else {
            $likeService->execute($photo);
            $this->addFlash('success', 'Photo liked!');
        }

        return $this->redirectToRoute('home');
    }

    #[Route('/photo/import-from-phoenix', name: 'photo_import_from_phoenix', methods: ['POST'])]
    public function importFromPhoenix(
        PhotoRepository $photoRepository,
        PhoenixApiClientInterface $phoenixApiClient,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $token = $user->getPhoenixToken();
        if (!$token) {
            $this->addFlash('warning', 'No Phoenix API token set. Please save your token first.');
            return $this->redirectToRoute('profile');
        }

        try {
            $remotePhotos = $phoenixApiClient->fetchPhotos($token);
        } catch (\RuntimeException $e) {
            $previousException = $e->getPrevious();
            if ($previousException instanceof ClientException && $previousException->getCode() === 401) {
                $this->addFlash('error', 'Unauthorized API token. Save correct user token and try again.');
            } else {
                $this->addFlash('error', 'Could not connect to Phoenix API. Please try again later.');
            }

            return $this->redirectToRoute('profile');
        }

        $existingIds = $photoRepository->findExistingPhoenixPhotoIds($user);
        $newPhotos = [];
        foreach ($remotePhotos as $remote) {
            if (in_array($remote->id, $existingIds, true)) {
                continue;
            }
            $newPhotos[] = self::createPhoto($user, $remote);
        }

        $photoRepository->saveAll($newPhotos);
        $count = count($newPhotos);

        $this->addFlash('success', $count > 0
            ? "Imported {$count} new photo(s) from Phoenix."
            : 'No new photos to import.'
        );

        return $this->redirectToRoute('profile');
    }

    private static function createPhoto(User $user, PhotoEntryDto $remoteDto): Photo
    {
        return (new Photo())
            ->setUser($user)
            ->setImageUrl($remoteDto->photoUrl)
            ->setPhoenixPhotoId($remoteDto->id)
            ->setCamera($remoteDto->camera)
            ->setLocation($remoteDto->location)
            ->setDescription($remoteDto->description)
            ->setTakenAt($remoteDto->takenAt);
    }
}
