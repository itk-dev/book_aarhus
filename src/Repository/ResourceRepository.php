<?php

namespace App\Repository;

use App\Entity\Main\Resource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Main\Resource>
 */
class ResourceRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly CvrWhitelistRepository $cvrWhitelistRepository,
    ) {
        parent::__construct($registry, Resource::class);
    }

    public function add(Resource $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByEmail(string $email): ?Resource
    {
        $resource = $this->findOneBy(['resourceMail' => $email]);

        if ($resource instanceof Resource) {
            return $resource;
        }

        return null;
    }

    public function getAllByPermission(?string $permission = null, ?bool $includeInUI = null, array $excludedResources = []): array
    {
        $qb = $this->createQueryBuilder('res');

        if (null !== $includeInUI) {
            $qb->andWhere($qb->expr()->eq('res.includeInUI', ':includeInUI'));
            $qb->setParameter('includeInUI', $includeInUI);
        }

        if ('citizen' == $permission) {
            $qb->andWhere($qb->expr()->eq('res.permissionCitizen', true));
        } elseif ('businessPartner' == $permission) {
            $qb->andWhere($qb->expr()->eq('res.permissionBusinessPartner', true));
        } else {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('res.permissionBusinessPartner', true),
                $qb->expr()->eq('res.permissionCitizen', true)
            ));
        }

        if (!empty($excludedResources)) {
            $qb->andWhere($qb->expr()->notIn('res.resourceMail', $excludedResources));
        }

        $qb->andWhere($qb->expr()->neq('res.hasWhitelist', true));

        return $qb->getQuery()->getResult();
    }

    public function getOnlyWhitelisted(?string $permission = null, ?string $whitelistKey = null, array $excludedResources = [])
    {
        $qb = $this->createQueryBuilder('res');

        if ('citizen' == $permission) {
            $qb->andWhere($qb->expr()->eq('res.permissionCitizen', true));
        } elseif ('businessPartner' == $permission) {
            $qb->andWhere($qb->expr()->eq('res.permissionBusinessPartner', true));
        }

        if (!empty($excludedResources)) {
            $qb->andWhere($qb->expr()->notIn('res.resourceMail', $excludedResources));
        }

        $subQueryBuilder = $this->cvrWhitelistRepository->createQueryBuilder('w');
        $subQuery = $subQueryBuilder->where('w.cvr = :whitelist')->andWhere('w.resource = res.id');

        $qb->andWhere(
            $qb->expr()->andX(
                $qb->expr()->eq('res.hasWhitelist', true),
                $qb->expr()->exists($subQuery),
            )
        )->setParameter('whitelist', $whitelistKey);

        return $qb->getQuery()->getResult();
    }

    public function getExistingSourceIds(): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e.sourceId');
        return $qb->getQuery()->getSingleColumnResult();
    }
}
