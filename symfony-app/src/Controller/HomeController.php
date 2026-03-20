<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\PhotoFiltersType;
use App\Repository\LikeRepository;
use App\Repository\PhotoRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    #[Template('home/index.html.twig')]
    public function index(Request $request, PhotoRepository $photoRepository, LikeRepository $likeRepository): array
    {
        $form = $this->createForm(PhotoFiltersType::class);
        $form->handleRequest($request);

        $filters = $form->isSubmitted() && $form->isValid() ? $form->getData() : null;
        $photos = $photoRepository->findAllWithUsers($filters);

        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        $likedPhotoIds = ($currentUser instanceof User)
            ? $likeRepository->getLikedPhotoIds($currentUser)
            : [];

        return [
            'photos' => $photos,
            'filterForm' => $form->createView(),
            'currentUser' => $currentUser,
            'likedPhotoIds' => $likedPhotoIds,
        ];
    }
}
