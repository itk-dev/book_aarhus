<?php

namespace App\Repository;

use App\Entity\Main\CvrWhitelist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Main\CvrWhitelist>
 */
class CvrWhitelistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CvrWhitelist::class);
    }

    public function getExistingSourceIds()
    {
        $query = 'SELECT id,source_id FROM cvr_whitelist';
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        return $stmt->executeQuery()->fetchAllKeyValue();
    }
}
