<?php

namespace App\Repository;

use App\Entity\Main\UserBooking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserBooking>
 *
 * @method UserBooking|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserBooking|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserBooking[]    findAll()
 * @method UserBooking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserBookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBooking::class);
    }
}
