<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\EventSubscriber;

use App\Service\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PreAuthenticatedUserBadge;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

/**
 * MeteoCheckerListener
 */
class MeteoCheckerListener implements EventSubscriberInterface
{
    public function __construct(
        private UserCheckerInterface $userChecker,
        private UserService $userService,
        ) {
    }

    public function preCheckCredentials(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();

        if ($passport->hasBadge(PreAuthenticatedUserBadge::class))
            return;

        try {
            $user = $passport->getUser();
        } catch (\Exception $e) {
            $user = null;
        }

        if ($user === null) {
            $uid = (array_values($passport->getBadges())[0])->getUserIdentifier();
            $user = $this->userService->createUser($uid);
        }
        $this->userChecker->checkPreAuth($user);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['preCheckCredentials', 2000],
        ];
    }
}
