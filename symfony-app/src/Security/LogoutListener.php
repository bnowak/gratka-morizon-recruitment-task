<?php

declare(strict_types = 1);

namespace App\Security;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

#[AsEventListener]
class LogoutListener
{
    public function __invoke(LogoutEvent $event): void
    {
        $session = $event->getRequest()->getSession();
        assert($session instanceof FlashBagAwareSessionInterface);
        $session->getFlashBag()->add('info', 'You have been logged out successfully.');
    }
}
