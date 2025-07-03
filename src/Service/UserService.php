<?php

namespace App\Service;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use stdClass;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Bundle\SecurityBundle\Security;

class UserService
{
    public function __construct(private UserRepository $userRepository,private ContainerBagInterface $params, private Securizer $securizer)
    {
    }

    public function createUser($uid): User {
        $infos = $this->requestUidInfo($uid);

        if ($infos === null) {
            $user = $this->userRepository->createUser($uid, null, null, null);
        } else {
            $user = $this->userRepository->createUser($uid, $infos->displayName, $infos->mail, $infos->eduPersonPrimaryAffiliation);
        }

        return $user;
    }

    public function updateUser(User $user): User {
        $infos = $this->requestUidInfo($user->getUid());

        if (!$infos)
            return $user; 

        $infos->displayName == null ?: $user->setDisplayName($infos->displayName);
        !(isset($infos->mail) && $infos->mail !== null) ?: $user->setMail($infos->mail);

        if ( ! (isset($infos->eduPersonPrimaryAffiliation) && $infos->eduPersonPrimaryAffiliation !== null) )
            return $user;

        $user->setEduPersonPrimaryAffiliation($infos->eduPersonPrimaryAffiliation);

        if (! $this->securizer->isGranted($user, UserType::$choix[$infos->eduPersonPrimaryAffiliation]))
            $user->setRoles([UserType::$choix[$infos->eduPersonPrimaryAffiliation]]);

        return $user;
    }

    private function requestUidInfo(string $uid, $attrs = ['uid', 'displayName', 'mail', 'eduPersonPrimaryAffiliation']): ?stdClass {
        $urlwsgroup = $this->params->get('urlwsgroup_user_infos');

        $url = "$urlwsgroup?token=$uid&maxRows=1&attrs=". implode(',', $attrs);

        $fd = fopen($url, 'r');
        $ajaxReturn = stream_get_contents($fd);
        fclose($fd);

        // hack : remplace emeritus par teacher pour éviter de rajouter une catégorie supplémentaire de droits
        $ajaxReturn = str_replace('emeritus', 'teacher', $ajaxReturn);
        $arrayReturn = json_decode($ajaxReturn);

        if (count($arrayReturn) > 0) {
            foreach ($arrayReturn as $stdObj) {
                if ($stdObj->uid == $uid) {
                    return $stdObj;
                }
            }
        }

        return null;
    }
}
