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

    public function getExistingSourceIds()
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e.id', 'e.location');

        return array_reduce($qb->getQuery()->getArrayResult(), function ($result, $item) {
            $result[$item['id']] = $item['locatio '];
            return $result;
        }, []);
    }}
