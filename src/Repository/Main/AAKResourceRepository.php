<?php

namespace App\Repository\Main;

use App\Entity\Resources\AAKResource;
use App\Repository\Resources\CvrWhitelistRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AAKResourceRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly CvrWhitelistRepository $cvrWhitelistRepository
    ) {
        parent::__construct($registry, AAKResource::class);
    }

    public function add(AAKResource $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByEmail(string $email)
    {
        return $this->findOneBy(['resourceMail' => $email]);
    }

    public function findAllLocations(string $whitelistKey = null): array
    {
        $qb = $this->createQueryBuilder('res');

        $qb->select('res.location')
            ->where($qb->expr()->isNotNull('res.location'))
            ->andWhere($qb->expr()->neq('res.location', "''"))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('res.permissionCitizen', true),
                $qb->expr()->eq('res.permissionBusinessPartner', true),
            ));

        if (null == $whitelistKey) {
            $qb->andWhere($qb->expr()->neq('res.hasWhitelist', true));
        } else {
            $subQueryBuilder = $this->cvrWhitelistRepository->createQueryBuilder('w');
            $subQuery = $subQueryBuilder->where('w.cvr = :whitelist')->andWhere('w.resourceId = res.id');

            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->neq('res.hasWhitelist', true),
                    $qb->expr()->exists($subQuery),
                )
            )->setParameter('whitelist', $whitelistKey);
        }

        $qb->groupBy('res.location');

        return $qb->getQuery()->getArrayResult();
    }
}
