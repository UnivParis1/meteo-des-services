<?php

namespace App\Repository;

use App\Entity\Application;
use App\Entity\ApplicationHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Application>
 *
 * @method Application|null find($id, $lockMode = null, $lockVersion = null)
 * @method Application|null findOneBy(array $criteria, array $orderBy = null)
 * @method Application[]    findAll()
 * @method Application[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry                $registry,
                                private EntityManagerInterface $em)
    {
        parent::__construct($registry, Application::class);
    }

    public function insertApplicationWithFname(string $fname): Application
    {
        $application = new Application();
        $application->setFname($fname);
        $application->setIsFromJson(true);
        $application->setState("default");
        $this->em->persist($application);
        $this->em->flush();
        return $application;
    }

    public function createHistory(ApplicationHistory $history): ApplicationHistory
    {
        $this->em->persist($history);
        $this->em->flush();
        return $history;
    }

    public function updateApplication(Application $application): Application
    {
        $this->em->flush();
        return $application;
    }

    public function createApplication(Application $application): Application
    {
        $this->em->persist($application);
        $this->em->flush();
        return $application;
    }

    public function deleteApplication(Application $application)
    {
        $application->setIsArchived(true);
        $this->em->flush();
    }


//    /**
//     * @return Application[] Returns an array of Application objects
//     */
    public function findBySearchAndState(string $searchTerm, string $stateFilter): array
    {
        $query = $this->createQueryBuilder('a')
            ->andWhere('a.title LIKE :substring')
            ->setParameter('substring', '%' . $searchTerm . '%')
            ->andWhere('a.isArchived = :is_archived')
            ->setParameter('is_archived', false);
        if ($stateFilter != null && $stateFilter != 'all' && $stateFilter != '') {
            $query->andWhere('a.state = :state')
                ->setParameter('state', $stateFilter);
        }
        return $query->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    public function findOneBySomeField($value): ?Application
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
