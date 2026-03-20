<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    #[Template('profile/index.html.twig')]
    public function profile(): array
    {
        return [
            'user' => $this->getUser(),
        ];
    }

    #[Route('/profile/save-token', name: 'profile_save_token', methods: ['POST'])]
    public function saveToken(Request $request, UserRepository $userRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $token = $request->request->get('phoenix_token', '');
        $user->setPhoenixToken($token ?: null);

        $userRepository->save($user);

        $this->addFlash('success', 'Phoenix API token saved successfully.');

        return $this->redirectToRoute('profile');
    }
}
