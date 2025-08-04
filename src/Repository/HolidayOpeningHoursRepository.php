<?php

namespace App\Repository;

use App\Entity\Main\HolidayOpeningHours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Main\HolidayOpeningHours>
 */
class HolidayOpeningHoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HolidayOpeningHours::class);
    }

    public function getExistingSourceIds()
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e.id', 'e.sourceId');

        return array_reduce($qb->getQuery()->getArrayResult(), function ($result, $item) {
            $result[$item['id']] = $item['sourceId'];

            return $result;
        }, []);
    }
}
