<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Resources\AAKResource;
use App\Repository\Resources\CvrWhitelistRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

final class AAKResourceExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly CvrWhitelistRepository $cvrWhitelistRepository
    ) {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->applyResourceRequireLocation($queryBuilder, $resourceClass);
        $this->applyWhitelistPermission($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->applyResourceRequireLocation($queryBuilder, $resourceClass);
        $this->applyWhitelistPermission($queryBuilder, $resourceClass);
    }

    private function applyResourceRequireLocation(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (AAKResource::class !== $resourceClass || null === $this->security->getUser()) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere($queryBuilder->expr()->isNotNull(sprintf('%s.location', $rootAlias)))
            ->andWhere($queryBuilder->expr()->neq(sprintf('%s.location', $rootAlias), "''"));
    }

    private function applyWhitelistPermission(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (AAKResource::class !== $resourceClass) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        // Extract whitelistKey from request.
        $currentRequest = $this->requestStack->getCurrentRequest();
        $whitelistKey = $currentRequest?->query->get('whitelistKey');

        // If whitelistKey is set, check if the whitelistKey exists in cvrWhitelist for the given resource.
        if (null !== $whitelistKey) {
            $subQueryBuilder = $this->cvrWhitelistRepository->createQueryBuilder('w');
            $subQuery = $subQueryBuilder->where('w.cvr = :whitelist')->andWhere(sprintf('w.resourceId = %s.id', $rootAlias));

            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->neq(sprintf('%s.hasWhitelist', $rootAlias), true),
                    $queryBuilder->expr()->exists($subQuery),
                )
            )->setParameter('whitelist', $whitelistKey);
        } else {
            $queryBuilder->andWhere($queryBuilder->expr()->neq(sprintf('%s.hasWhitelist', $rootAlias), true));
        }
    }
}
