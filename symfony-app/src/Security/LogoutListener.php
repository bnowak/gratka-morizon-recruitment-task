<?php

namespace App\Security;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LogoutEvent;

#[AsEventListener]
class LogoutListener
{
    public function __invoke(LogoutEvent $event): void
    {
        $event->getRequest()->getSession()->getFlashBag()->add('info', 'You have been logged out successfully.');
    }
}
