<?php

namespace App\Repository;

use App\Entity\ApplicationHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApplicationHistory>
 *
 * @method ApplicationHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApplicationHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApplicationHistory[]    findAll()
 * @method ApplicationHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApplicationHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $em)
    {
        parent::__construct($registry, ApplicationHistory::class);
    }

    public function saveHistory(ApplicationHistory $history): void
    {
        $this->em->persist($history);
        $this->em->flush();
    }

    //    /**
    //     * @return ApplicationHistory[] Returns an array of ApplicationHistory objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ApplicationHistory
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
