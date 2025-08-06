<?php

namespace App\Repository;

use App\Entity\Main\Location;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Main\Location>
 */
class LocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }

    public function getExistingSourceIds(): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e.location');
        return $qb->getQuery()->getSingleColumnResult();
    }
}
