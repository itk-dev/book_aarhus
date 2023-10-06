<?php

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Main\UserBookingCacheEntry;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Security;

final class UserBookingCacheEntryExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
    private readonly Security $security,
    private readonly RequestStack $requestStack,
  ) {
    }

    /**
     * {@inheritdoc}
     */
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    /**
     * WHere condition to ensure display of users personal bookings.
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $resourceClass
     *
     * @return void
     */
    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (UserBookingCacheEntry::class !== $resourceClass) {
            return;
        }
        
        $currentRequest = $this->requestStack->getCurrentRequest();
        $userId = $currentRequest?->headers?->get('authorization-userid');

        if (empty($userId)) {
            throw new UnauthorizedHttpException('authorization-userid', 'Missing userid in header');
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere(sprintf('%s.uid = :uid', $rootAlias));
        $queryBuilder->setParameter('uid', $userId);
    }
}
