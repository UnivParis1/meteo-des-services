<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Entity\Application;
use App\Service\ApplicationService;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    public function __construct(private UserRepository $userRepository,
                                private ApplicationService $applicationService) {}

    public function onBeforeEntityUpdatedEvent($event): void
    {
        if (($user = $event->getEntityInstance()) instanceof User) {
            $this->userRepository->updateUserRequestInfos($user);
            return;
        }
    }

    public function onBeforeEntityPersistedEvent($event): void {
        if (! ($user = $event->getEntityInstance()) instanceof User)
            return;

        $this->userRepository->updateUserRequestInfos(user: $user, isUpdate: false);
    }

    public function onAfterEntityPersistedEvent(AfterEntityPersistedEvent $event): void {
        if (($application = $event->getEntityInstance()) instanceof Application) {
            $this->applicationService->updateHistory($application, 'creation');
        }

    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityUpdatedEvent::class => ['onBeforeEntityUpdatedEvent', 0],
            BeforeEntityPersistedEvent::class => ['onBeforeEntityPersistedEvent', 0],
            AfterEntityPersistedEvent::class => ['onAfterEntityPersistedEvent', 0]
        ];
    }
}
