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
            $user = $this->userRepository->createUser($uid, $infos->displayName ?? null, $infos->mail ?? null, $infos->eduPersonAffiliation ?? null);
        }

        return $user;
    }

    public function updateUserRequestInfos(User $user): User {
        $infos = $this->requestUidInfo($user->getUid());

        if (!$infos)
            return $user; 

        $infos->displayName == null ?: $user->setDisplayName($infos->displayName);
        !(isset($infos->mail) && $infos->mail !== null) ?: $user->setMail($infos->mail);

        if ( ! (isset($infos->eduPersonAffiliation) && $infos->eduPersonAffiliation !== null) )
            return $user;

        $user->setEduPersonAffiliations($infos->eduPersonAffiliation);

        foreach ($infos->eduPersonAffiliation as $affiliation) {
            if (!isset(UserType::$choix[$affiliation]))
                continue;

            if (! $this->securizer->isGranted($user, UserType::$choix[$affiliation])) {
                $user->setRoles([UserType::$choix[$affiliation]]);
                break;
            }
        }

        return $user;
    }

    private function requestUidInfo(string $uid, $attrs = ['uid', 'displayName', 'mail', 'eduPersonAffiliation']): ?stdClass {
        $urlwsgroup = $this->params->get('urlwsgroup_user_infos');

        $url = "$urlwsgroup?token=$uid&maxRows=1&attrs=". implode(',', $attrs);

        $fd = fopen($url, 'r');
        $ajaxReturn = stream_get_contents($fd);
        fclose($fd);

        // hack : remplace emeritus par teacher pour éviter de rajouter une catégorie supplémentaire de droits
        $ajaxReturn = str_replace('emeritus', 'teacher', $ajaxReturn);
        $ajaxReturn = str_replace('researcher', 'teacher', $ajaxReturn);

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

    public function searchUserOrGroup(string $search, $attrs=['uid','mail','displayName','cn','employeeType','departmentNumber','eduPersonPrimaryAffiliation','supannEntiteAffectation-ou','supannRoleGenerique','supannEtablissement']): ?array {
        $urlwsgroup = $this->params->get('urlwsgroup_user_infos');

        // recopié depuis creneaux (recherche utilisateur ou groupes)
        $url = "https://wsgroups.univ-paris1.fr/search?maxRows=10&user_attrs=". implode(',', $attrs) ."&filter_category=groups&filter_group_cn=employees.*&filter_eduPersonAffiliation=teacher|researcher|staff|emeritus&token=$search";

        $fd = fopen($url, 'r');
        $ajaxReturn = stream_get_contents($fd);
        fclose($fd);

        $stdResponse= json_decode($ajaxReturn);

        if (is_array($stdResponse->users) && count($stdResponse->users) > 0) {
            // vérifier uid,displayName et mail présent
            return $stdResponse->users; //$stdResponse->users;
        }
        elseif (is_array($stdResponse->groups) && count($stdResponse->groups) > 0) {
            foreach ($stdResponse->groups as $group) {
                if ($group->category == "groups_structures") {
                    $groupSearch = $group->key;
                    $urlRecuperationsMails = "https://wsgroups.univ-paris1.fr/searchUser?key=$groupSearch&filter_member_of_group=$groupSearch&filter_mail=*&maxRows=100&attrs=uid%2CdisplayName%2Cmail";
                    $fd = fopen($urlRecuperationsMails, 'r');
                    $mailsjson = stream_get_contents($fd);
                    fclose($fd);

                    $users = json_decode($mailsjson);
                    if (count($users) > 0) {
                        return $users;
                    }
                }
            }
        }

        return null;
    }

    public function createOrUpdateUserRole(stdClass $userStd, string $role) : void
    {
        $user = $this->userRepository->findOneBy(["uid" => $userStd->uid]) ?? $this->userRepository->createUser($userStd->uid, $userStd->displayName, $userStd->mail, null);

        $user->setRoles([$role]);
        $this->userRepository->updateUser($user);
    }
}
