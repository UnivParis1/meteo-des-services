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
        $query = $this->getEntityManager()->createQueryBuilder()->select('a')
            ->from('App\Entity\Application', 'a')
            ->leftJoin('App\Entity\ViewMaintenanceEnCours', 'm', \Doctrine\ORM\Query\Expr\Join::WITH, 'a.id = m.application')
            ->where('a.isArchived = 0');

        if ($stateFilter != null && $stateFilter != 'all' && $stateFilter != '') {
            $query->andWhere("a.state = '$stateFilter'")->orWhere("m.applicationState = '$stateFilter'");
        }

        if (strlen($searchTerm) > 0)
            $query->andWhere("a.title LIKE '%$searchTerm%'");

        $dql = $query->orderBy('a.title', 'ASC')
            ->getQuery();

        $results = $dql->getResult();

        return $results;
    }

    public function findAllNotArchived() : array
    {
        return $this->createQueryBuilder('a')->where('a.isArchived = 0')->orderBy('a.title', 'ASC')->getQuery()->getResult();
    }
}
