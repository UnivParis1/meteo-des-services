<?php

namespace App\Repository;

use App\Entity\Application;
use App\Entity\ApplicationHistory;
use App\Entity\Tags;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
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
    public function __construct(ManagerRegistry $registry,
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

    public function deleteApplication(Application $application): void
    {
        $application->setIsArchived(true);
        $this->em->flush();
    }

    public function removeApplicationTags(Application $application): void
    {
        foreach ($application->getTags() as $tags) {
            $application->removeTag($tags);
        }
        $this->em->flush();
    }

    //    /**
    //     * @return Application[] Returns an array of Application objects
    //     */
    public function findBySearchAndState(string $searchTerm, string $stateFilter): array
    {
        $query = $this->createQueryBuilder('a')
                      ->leftJoin('App\Entity\ViewMaintenanceEnCours', 'm', \Doctrine\ORM\Query\Expr\Join::ON, 'a.id = m.application')
                      ->where('a.isArchived = 0');

        if (null != $stateFilter && 'all' != $stateFilter && '' != $stateFilter)
            $query->andWhere("m.applicationState = '$stateFilter' OR (a.state = '$stateFilter' AND m.id IS NULL)");

        if (strlen($searchTerm) > 0)
            $query->andWhere("a.title LIKE '%$searchTerm%'");

         return $query->orderBy('a.title', 'ASC')
                      ->getQuery()
                      ->getResult();
    }

    public function findAllNotArchived(): array
    {
        return $this->createQueryBuilder('a')
                    ->where('a.isArchived = 0')
                    ->orderBy('a.title', 'ASC')
                    ->getQuery()
                    ->getResult();
    }

    public function findByTagsAndState(Tags $tag, string $stateFilter): array
    {
        $query = $this->createQueryBuilder('a')
                      ->join('a.tags', 't')
                      ->leftJoin('App\Entity\ViewMaintenanceEnCours', 'm', \Doctrine\ORM\Query\Expr\Join::ON, 'a.id = m.application')
                      ->where('t = :tags')
                      ->andWhere('a.isArchived = 0')
                      ->setParameter('tags', $tag);

        if (null != $stateFilter && 'all' != $stateFilter && '' != $stateFilter)
            $query->andWhere("m.applicationState = '$stateFilter' OR (a.state = '$stateFilter' AND m.id IS NULL)");

        return $query->orderBy('a.title', 'ASC')
                     ->getQuery()
                     ->getResult();
    }
}
