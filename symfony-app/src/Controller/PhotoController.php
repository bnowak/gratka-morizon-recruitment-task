<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\User;
use App\Repository\PhotoRepository;
use App\Service\LikeService;
use App\Service\PhoenixService\Dto\PhotoEntryDto;
use App\Service\PhoenixService\Exception\PhoenixApiException;
use App\Service\PhoenixService\Exception\PhoenixRateLimitException;
use App\Service\PhoenixService\Exception\PhoenixUnauthorizedException;
use App\Service\PhoenixService\PhoenixApiClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class PhotoController extends AbstractController
{
    #[Route('/photo/{id}/like', name: 'photo_like', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function like(
        int $id,
        LikeService $likeService,
        PhotoRepository $photoRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $photo = $photoRepository->find($id);

        if (!$photo) {
            throw $this->createNotFoundException('Photo not found');
        }

        try {
            if ($likeService->hasUserLikedPhoto($photo, $user)) {
                $likeService->unlike($photo, $user);
                $this->addFlash('info', 'Photo unliked!');
            } else {
                $likeService->like($photo, $user);
                $this->addFlash('success', 'Photo liked!');
            }
        } catch (\RuntimeException $e) {
            $this->addFlash('error', sprintf('%s Please try again.', $e->getMessage()));
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
        } catch (PhoenixApiException $e) {
            $errorMessage = match ($e::class) {
                PhoenixUnauthorizedException::class => 'Unauthorized API token. Save correct user token and try again.',
                PhoenixRateLimitException::class => 'Too many import requests. Please try again later.',
                default => 'Could not connect to Phoenix API. Please try again later.',
            };

            $this->addFlash('error', $errorMessage);
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
            : 'No new photos to import.');

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
