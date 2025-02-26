<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use stdClass;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class UserService
{
    public function __construct(private UserRepository $userRepository,private ContainerBagInterface $params)
    {
    }

    public function createUser($uid): User {
        $infos = $this->requestUidInfo($uid);

        if ($infos === null) {
            $user = $this->userRepository->createUser($uid, null, null);
        } else {
            $user = $this->userRepository->createUser($uid, $infos->displayName, $infos->mail);
        }

        return $user;
    }

    private function requestUidInfo(string $uid): ?stdClass {
        $urlwsgroup = $this->params->get('urlwsgroup_user_infos');

        $url = "$urlwsgroup?token=$uid&maxRows=1&attrs=uid,displayName,mail";

        $fd = fopen($url, 'r');
        $ajaxReturn = stream_get_contents($fd);
        fclose($fd);

        $arrayReturn = json_decode($ajaxReturn);

        if ($ajaxReturn[0]) {
            foreach ($arrayReturn as $stdObj) {
                if ($stdObj->uid == $uid) {
                    return $stdObj;
                }
            }
        }

        return null;
    }
}
