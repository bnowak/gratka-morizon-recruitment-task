<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\PhotoFiltersType;
use App\Likes\LikeRepository;
use App\Repository\PhotoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @return JsonResponse
     */
    public function index(Request $request, PhotoRepository $photoRepository, LikeRepository $likeRepository): Response
    {
        $form = $this->createForm(PhotoFiltersType::class);
        $form->handleRequest($request);

        $filters = $form->isSubmitted() && $form->isValid() ? $form->getData() : null;
        $photos = $photoRepository->findAllWithUsers($filters);

        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        $userLikes = [];

        if ($currentUser instanceof User) {
            foreach ($photos as $photo) {
                $userLikes[$photo->getId()] = $likeRepository->hasUserLikedPhoto($photo, $currentUser);
            }
        }

        return $this->render('home/index.html.twig', [
            'photos' => $photos,
            'filterForm' => $form->createView(),
            'currentUser' => $currentUser,
            'userLikes' => $userLikes,
        ]);
    }
}
