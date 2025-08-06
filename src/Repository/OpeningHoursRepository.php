<?php

namespace App\Repository;

use App\Entity\Main\OpeningHours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Main\OpeningHours>
 */
class OpeningHoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OpeningHours::class);
    }

    public function getExistingSourceIds(): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e.sourceId');
        return $qb->getQuery()->getSingleColumnResult();
    }
}
