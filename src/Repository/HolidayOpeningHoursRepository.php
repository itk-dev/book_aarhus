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
        $query = 'SELECT id,source_id FROM holiday_opening_hours';
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        return $stmt->executeQuery()->fetchAllKeyValue();
    }
}
