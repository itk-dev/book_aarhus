<?php

namespace App\Repository\Resources;

use App\Entity\Resources\CvrWhitelist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CvrWhitelistRepository>
 *
 * @method resource|null find($id, $lockMode = null, $lockVersion = null)
 * @method resource|null findOneBy(array $criteria, array $orderBy = null)
 * @method resource[]    findAll()
 * @method resource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CvrWhitelistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CvrWhitelist::class);
    }
}
