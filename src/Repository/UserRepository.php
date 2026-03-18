<?php

namespace App\Repository;

use App\Entity\User;
use App\Model\UserRoles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public $isCalled = false;

    public function __construct(
        private ManagerRegistry $registry,
        private EntityManagerInterface $em,
        private ContainerBagInterface $params
    ) {
        parent::__construct($registry, User::class);
    }

    public function createUser($uid): User
    {
        $infos = $this->requestUidInfo($uid);

        if (! ($infos && $infos->eduPersonAffiliation)) {
            throw new Exception('Appel wsgroups erreur requestUidInfo');
        } else {
            $user = $this->createUserEntity($uid, $infos->displayName ?? null, $infos->mail ?? null, $infos->eduPersonAffiliation);
        }

        return $user;
    }

    public function createUserEntity(string $uid, ?string $displayName, ?string $mail, ?array $eduPersonAffiliations): User
    {
        $u = new User;
        $u->setUid($uid);

        $u->setMail($mail);
        $u->setDisplayName($displayName);
        $u->setEduPersonAffiliations($eduPersonAffiliations);

        $em = $this->getEntityManager();
        $em->persist($u);
        $em->flush();

        return $u;
    }

    public function updateUser(User $user): User
    {
        $this->em->flush();

        return $user;
    }

    public function findOne(User $user): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByUid(string $uid): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.uid = :uid')
            ->setParameter('uid', $uid)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function updateUserRequestInfos(User $user): User
    {
        $infos = $this->requestUidInfo($user->getUid());

        if (!$infos) {
            return $user;
        }

        null == $infos->displayName ?: $user->setDisplayName($infos->displayName);
        !(isset($infos->mail) && null !== $infos->mail) ?: $user->setMail($infos->mail);

        if (!(isset($infos->eduPersonAffiliation) && null !== $infos->eduPersonAffiliation)) {
            return $user;
        }

        $user->setEduPersonAffiliations($infos->eduPersonAffiliation);

        $choicesIndexedArray = array_values(UserRoles::$choix);
        $affiliationIndexedArray = array_values(UserRoles::$easyAdminEduAffiliations);

        $maxPermissionIndex = 0;
        foreach ($infos->eduPersonAffiliation as $affiliation) {
            if (!isset(UserRoles::$easyAdminEduAffiliations[$affiliation])) {
                continue;
            }

            $indexPermission = array_search($affiliation, $affiliationIndexedArray);

            assert($indexPermission, "Erreur droits inconnus $affiliation");

            // incrémente l'index car les permissions commencent en anonmyme avec l'index 0
            ++$indexPermission;

            if ($indexPermission > $maxPermissionIndex) {
                $maxPermissionIndex = $indexPermission;
            } else {
                continue;
            }
        }

        if ($maxPermissionIndex > 0) {
            $isGranted = $choicesIndexedArray[$maxPermissionIndex];
            $roles = $user->getRoles();

            if (!in_array($isGranted, $roles)) {
                $roles[] = $isGranted;
                $user->setRoles($roles);
                $this->updateUser($user);
            }
        }



        return $user;
    }

    public function requestUidInfo(string $uid, $attrs = ['uid', 'displayName', 'mail', 'eduPersonAffiliation']): ?\stdClass
    {
        $urlwsgroup = $this->params->get('urlwsgroup_user_infos');

        $url = "$urlwsgroup?token=$uid&maxRows=1&attrs=" . implode(',', $attrs);

        $fd = fopen($url, 'r');
        $ajaxReturn = stream_get_contents($fd);
        fclose($fd);

        // hack : remplace emeritus par teacher pour éviter de rajouter une catégorie supplémentaire de droits
        $ajaxReturn = str_replace('emeritus', 'teacher', $ajaxReturn);
        $ajaxReturn = str_replace('researcher', 'teacher', $ajaxReturn);

        $arrayReturn = json_decode($ajaxReturn);

        if (is_array($arrayReturn) && count($arrayReturn) > 0) {
            foreach ($arrayReturn as $stdObj) {
                if ($stdObj->uid == $uid) {
                    return $stdObj;
                }
            }
        } elseif (is_object($arrayReturn) && $arrayReturn instanceof \stdClass) {
            if (is_array($arrayReturn->users) && count($arrayReturn->users) > 0) {
                foreach ($arrayReturn->users as $user) {
                    if ($user->uid == $uid) {
                        return $user;
                    }
                }
            }
        }

        return null;
    }

    public function searchUserOrGroup(string $search, $attrs = ['uid', 'mail', 'displayName', 'cn', 'employeeType', 'departmentNumber', 'eduPersonPrimaryAffiliation', 'supannEntiteAffectation-ou', 'supannRoleGenerique', 'supannEtablissement']): ?array
    {
        $urlwsgroup = $this->params->get('urlwsgroup_user_infos');

        // recopié depuis creneaux (recherche utilisateur ou groupes)
        $url = 'https://wsgroups.univ-paris1.fr/search?maxRows=10&user_attrs=' . implode(',', $attrs) . "&filter_category=groups&filter_group_cn=employees.*&filter_eduPersonAffiliation=teacher|researcher|staff|emeritus&token=$search";

        $fd = fopen($url, 'r');
        $ajaxReturn = stream_get_contents($fd);
        fclose($fd);

        $stdResponse = json_decode($ajaxReturn);

        if (is_array($stdResponse->users) && count($stdResponse->users) > 0) {
            // vérifier uid,displayName et mail présent
            return $stdResponse->users; // $stdResponse->users;
        } elseif (is_array($stdResponse->groups) && count($stdResponse->groups) > 0) {
            foreach ($stdResponse->groups as $group) {
                if ('groups_structures' == $group->category) {
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

    public function createOrUpdateUserRole(\stdClass $userStd, string $role): void
    {
        $user = $this->findOneBy(['uid' => $userStd->uid]) ?? $this->createUser($userStd->uid, $userStd->displayName, $userStd->mail, null);

        $user->setRoles([$role]);
        $this->updateUser($user);
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
