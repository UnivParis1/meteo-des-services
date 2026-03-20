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
    public function __construct(
        private ManagerRegistry $registry,
        private EntityManagerInterface $em,
        private ContainerBagInterface $params
    ) {
        parent::__construct($registry, User::class);
    }

    public function createUser($uid): User
    {
        return $this->createUserEntity($uid);
    }

    public function createUserEntity(string $uid): User
    {
        $u = new User;
        $u->setUid($uid);

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

    public function updateUserRequestInfos(User $user, $isUpdate = true): void
    {
        $urlwsgroup = $this->params->get('urlwsgroup_user_infos');
        $s1 = serialize($user);
        $user = self::_updateUserRequestInfos($user, $urlwsgroup, $isUpdate);
        $s2 = serialize($user);
        // compare les 2 string sérialisés pour vérifier des changements
        if ($s1 <> $s2)
            $this->updateUser($user);
    }

    private static function _updateUserRequestInfos(User $user, $urlwsgroup, $isUpdate = true): User
    {
        // test si un role superviseur ou admin est trouvé, si oui, assignation d'un seul role au user (les roles étant hierarchiques)
        // stop la mise à jour et la recherche des roles
        if ($suOrAdminRole = self::testDroitSuperviseurOuAdmin($user)) {
            if (!in_array($suOrAdminRole, $user->getRoles()))
                $user->setRoles([$suOrAdminRole]);
            if ($isUpdate)
                return $user;
        }
        $infos = self::requestUidInfo($user->getUid(), $urlwsgroup);

        if (!$infos)
            return $user;

        !(isset($infos->displayName) && null !== $infos->displayName) ?: $user->setDisplayName($infos->displayName);
        !(isset($infos->mail) && null !== $infos->mail) ?: $user->setMail($infos->mail);

        if (!(isset($infos->eduPersonAffiliation) && null !== $infos->eduPersonAffiliation))
            return $user;

        if ($user->getEduPersonAffiliations() <> $infos->eduPersonAffiliation)
            $user->setEduPersonAffiliations($infos->eduPersonAffiliation);

        if ($role = UserRoles::roleMaxHorsAdmins($infos->eduPersonAffiliation))
            $user->setRoles([$role]);

        return $user;
    }

    private static function testDroitSuperviseurOuAdmin(User $user): ?string
    {
        foreach (array_reverse(UserRoles::$droitsAdminEtSuperviseur) as $is => $role)
            if (isset($user->$is) && $user->$is)
                return $role;
        return null;
    }

    public static function requestUidInfo(string $uid, string $urlwsgroup, $attrs = ['uid', 'displayName', 'mail', 'eduPersonAffiliation']): ?\stdClass
    {
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
