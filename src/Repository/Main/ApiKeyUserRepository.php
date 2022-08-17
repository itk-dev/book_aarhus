<?php

namespace App\Repository\Main;

use App\Entity\Main\ApiKeyUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApiKeyUser>
 *
 * @method ApiKeyUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApiKeyUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApiKeyUser[]    findAll()
 * @method ApiKeyUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApiKeyUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiKeyUser::class);
    }

    public function add(ApiKeyUser $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ApiKeyUser $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ApiKeyUser[] Returns an array of ApiKeyUser objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ApiKeyUser
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
