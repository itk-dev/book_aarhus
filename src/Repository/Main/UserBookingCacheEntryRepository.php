<?php

namespace App\Repository\Main;

use App\Entity\Main\UserBookingCacheEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserBookingCacheEntry>
 *
 * @method UserBookingCacheEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserBookingCacheEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserBookingCacheEntry[]    findAll()
 * @method UserBookingCacheEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserBookingCacheEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBookingCacheEntry::class);
    }
}
