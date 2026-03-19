<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    public function __construct(private UserRepository $userRepository) {}

    public function onBeforeEntityUpdatedOrPersistEvent($event): void
    {
        if (! ($user = $event->getEntityInstance()) instanceof User)
            return;

        $this->userRepository->updateUserRequestInfos($user);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityUpdatedEvent::class => ['onBeforeEntityUpdatedOrPersistEvent', 2000],
            BeforeEntityPersistedEvent::class => ['onBeforeEntityUpdatedOrPersistEvent', 2000],
        ];
    }
}
