<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\AuthTokenRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class TokenUrlAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private AuthTokenRepository $authTokenRepository,
        private UserRepository $userRepository,
        private RouterInterface $router,
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->get('_route') === 'auth_login';
    }

    public function authenticate(Request $request): Passport
    {
        /**
         * Additionally, we could handle use-cases here when username and/or token is not provided/is empty
         */
        $username = $request->get('username') ?? '';
        $token = $request->get('token') ?? '';

        if (!$this->authTokenRepository->findByToken($token)) {
            throw new CustomUserMessageAuthenticationException('Invalid token');
        }

        return new SelfValidatingPassport(
            new UserBadge(
                $username,
                fn (string $identifier) => $this->userRepository->findByUsername($identifier)
                    ?? throw new CustomUserMessageAuthenticationException('User not found'),
            )
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $username = $token->getUser()->getUserIdentifier();
        $request->getSession()->getFlashBag()->add('success', 'Welcome back, ' . $username . '!');

        return new RedirectResponse($this->router->generate('home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response($exception->getMessageKey(), 401);
    }
}
