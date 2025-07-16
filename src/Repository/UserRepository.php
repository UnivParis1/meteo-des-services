<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(private ManagerRegistry $registry,
                               private EntityManagerInterface $em)
    {
        parent::__construct($registry, User::class);
    }

    public function createUser(string $uid, ?string $displayName, ?string $mail, ?array $eduPersonAffiliations): User
    {
            $u = new User();
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
