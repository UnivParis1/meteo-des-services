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

    public function onBeforeEntityUpdatedEvent($event): void
    {
        if (! ($user = $event->getEntityInstance()) instanceof User)
            return;

        $this->userRepository->updateUserRequestInfos($user);
    }

    public function onBeforeEntityPersistedEvent($event): void {
        if (! ($user = $event->getEntityInstance()) instanceof User)
            return;

        $this->userRepository->updateUserRequestInfos(user: $user, isUpdate: false);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityUpdatedEvent::class => ['onBeforeEntityUpdatedEvent', 0],
            BeforeEntityPersistedEvent::class => ['onBeforeEntityPersistedEvent', 0],
        ];
    }
}
