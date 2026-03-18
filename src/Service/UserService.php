<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;

class UserService
{
    public function __construct(private UserRepository $userRepository) {}

    public function createUser($uid): User
    {
        return $this->userRepository->createUser($uid);
    }

    public function updateUserRequestInfos($user)
    {
        $this->userRepository->updateUserRequestInfos($user);
    }
}
