<?php

namespace App\Service;

use stdClass;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class UserService
{
    public function __construct(private UserRepository $userRepository, private ContainerBagInterface $params) {}

    public function createUser($uid): User
    {
        return $this->userRepository->createUser($uid);
    }

    public function updateUserRequestInfos($user)
    {
        $this->userRepository->updateUserRequestInfos($user);
    }

    public function requestWS($uid): ?stdClass
    {
        return UserRepository::requestUidInfo($uid, $this->params->get('urlwsgroup_user_infos'));
    }
}
