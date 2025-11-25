<?php

namespace App\Repository;

use App\Entity\Maintenance;
use App\Entity\MaintenanceHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Maintenance>
 *
 * @method Maintenance|null find($id, $lockMode = null, $lockVersion = null)
 * @method Maintenance|null findOneBy(array $criteria, array $orderBy = null)
 * @method Maintenance[]    findAll()
 * @method Maintenance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaintenanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry,
        private EntityManagerInterface $em)
    {
        parent::__construct($registry, Maintenance::class);
    }

    public function createHistory(MaintenanceHistory $history): MaintenanceHistory
    {
        $this->em->persist($history);
        $this->em->flush();

        return $history;
    }

    public function createMaintenance(Maintenance $maintenance): Maintenance
    {
        $this->em->persist($maintenance);
        $this->em->flush();

        return $maintenance;
    }

    public function updateMaintenance(Maintenance $maintenance): void
    {
        $this->em->flush();
    }

    public function findNextMaintenancesFromApplication(int $applicationId, int $maxNbOfMaintenances = 0): array
    {
        $now = new \DateTime();
        $query = $this->createQueryBuilder('m')
            ->join('m.application', 'a')
            ->andWhere('a.id = :appId')
            ->setParameter('appId', $applicationId)
            ->andWhere('m.isArchived = :is_archived')
            ->setParameter('is_archived', false)
            ->andWhere('m.endingDate >= :now')
            ->setParameter('now', $now)
            ->orderBy('m.endingDate', 'ASC');
        if ($maxNbOfMaintenances > 0) {
            $query->setMaxResults($maxNbOfMaintenances);
        }

        return $query->getQuery()
            ->getResult();
    }

    public function deleteMaintenance(Maintenance $maintenance): void
    {
        $maintenance->setIsArchived(true);
        $this->em->flush();
    }

    //    /**
    //     * @return Maintenance[] Returns an array of Maintenance objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Maintenance
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
