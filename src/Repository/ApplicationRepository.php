<?php

namespace App\Repository;

use App\Entity\Application;
use App\Entity\ApplicationHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

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
                                private EntityManagerInterface $em,
                                private Security $security)
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

        // Les droits étant hierarchiques, si permission inférieure à ROLE_TEACHER on est sur ROLE_STUDENT donc filtre sur les applications
        if ( ! $this->security->isGranted('ROLE_TEACHER') ) {
            // obligé de passer par cast(a.roles) AS JSON pour faire un a.roles IS NULL (ajout de la lib beberlei/doctrineextensions pour avoir la fonction cast avec doctrine) pour une raison inconnue
            $query->andWhere("cast(a.roles AS CHAR) = cast('null' AS CHAR) OR a.roles LIKE '[]' OR a.roles LIKE '%ROLE_STUDENT%'");
        }

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
