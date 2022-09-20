<?php

namespace App\Repository\Main;

use App\Entity\Resources\AAKResource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AakResource>
 *
 * @method resource|null find($id, $lockMode = null, $lockVersion = null)
 * @method resource|null findOneBy(array $criteria, array $orderBy = null)
 * @method resource[]    findAll()
 * @method resource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AAKResourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AAKResource::class);
    }

    public function add(AAKResource $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByEmail(string $email): ?AAKResource
    {
        return $this->findOneBy(['resourceMail' => $email]);
    }

    public function findAllLocations(): array
    {
        $qb = $this->createQueryBuilder('res');

        // TODO: Allow locations if whitelistKey is set and set for resource.

        $qb->select('res.location')
            ->where($qb->expr()->neq('res.hasWhitelist', true))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('res.permissionCitizen', true),
                $qb->expr()->eq('res.permissionBusinessPartner', true),
            ))
            ->groupBy('res.location');

        return $qb->getQuery()->getArrayResult();
    }
}
