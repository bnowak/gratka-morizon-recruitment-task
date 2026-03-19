<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    public function profile(Request $request, UserRepository $userRepository): Response
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');

        if (!$userId) {
            return $this->redirectToRoute('home');
        }

        $user = $userRepository->find($userId);

        if (!$user) {
            $session->clear();
            return $this->redirectToRoute('home');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/save-token', name: 'profile_save_token', methods: ['POST'])]
    public function saveToken(Request $request, UserRepository $userRepository): Response
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');

        if (!$userId) {
            return $this->redirectToRoute('home');
        }

        $user = $userRepository->find($userId);

        if (!$user) {
            $session->clear();
            return $this->redirectToRoute('home');
        }

        $token = $request->request->get('phoenix_token', '');
        $user->setPhoenixToken($token ?: null);

        $userRepository->save($user);

        $this->addFlash('success', 'Phoenix API token saved successfully.');

        return $this->redirectToRoute('profile');
    }
}
