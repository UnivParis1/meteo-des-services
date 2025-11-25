<?php

namespace App\Repository;

use App\Entity\Tags;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tags>
 */
class TagsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry,
        private EntityManagerInterface $em)
    {
        parent::__construct($registry, Tags::class);
    }

    public function updateTags(Tags $tags): Tags
    {
        $this->em->flush();

        return $tags;
    }

    public function createTags(Tags $tags): Tags
    {
        $this->em->persist($tags);
        $this->em->flush();

        return $tags;
    }

    public function deleteTags(Tags $tags): void
    {
        $this->em->remove($tags);
        $this->em->flush();
    }
}
