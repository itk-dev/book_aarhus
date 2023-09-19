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

//    /**
//     * @return UserBookingCacheEntry[] Returns an array of UserBookingCacheEntry objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UserBookingCacheEntry
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
