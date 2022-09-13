<?php

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Resources\AAKResource;
use App\Repository\Resources\CvrWhitelistRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

final class AAKResourceExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security, private RequestStack $requestStack, private CvrWhitelistRepository $cvrWhitelistRepository)
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        $this->applyWhitelistPermission($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {
        $this->applyWhitelistPermission($queryBuilder, $resourceClass);
    }

    private function applyWhitelistPermission(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (AAKResource::class !== $resourceClass || null === $this->security->getUser()) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        // Extract whitelistKey from request.
        $currentRequest = $this->requestStack->getCurrentRequest();
        $whitelistKey = $currentRequest->query->get('whitelistKey');

        // If whitelistKey is set, check if the whitelistKey exists in cvrWhitelist for the given resource.
        if (null !== $whitelistKey) {
            $subQueryBuilder = $this->cvrWhitelistRepository->createQueryBuilder('w');
            $subQuery = $subQueryBuilder->where('w.cvr = :whitelist')->andWhere(sprintf('w.resourceId = %s.id', $rootAlias));

            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq(sprintf('%s.hasWhitelist', $rootAlias), 0),
                    $queryBuilder->expr()->exists($subQuery),
                )
            )->setParameter('whitelist', $whitelistKey);
        } else {
            $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('%s.hasWhitelist', $rootAlias), 0));
        }
    }
}
