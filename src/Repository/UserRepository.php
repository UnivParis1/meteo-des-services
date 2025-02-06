<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneBy(array $criteria, array|null $orderBy = null): object|null {
        $obj = parent::findOneBy($criteria);

        // creation du user si il n'est pas trouvé en bdd suite à requête cas, pas la méthode la plus élegante mais ... simple
        if (is_string($_REQUEST['ticket']) && str_contains($_REQUEST['ticket'], 'cas')  && $obj == null && count($criteria) == 1 && array_key_exists("uuid", $criteria)) {
            $u = new User();
            $u->setUuid($criteria['uuid']);
            $em = $this->getEntityManager();
            $em->persist($u);
            $em->flush();

            $obj = $u;
        }
        return $obj;
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
