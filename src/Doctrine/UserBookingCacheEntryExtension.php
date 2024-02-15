<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Main\UserBookingCacheEntry;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

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
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    /**
     * WHere condition to ensure display of users personal bookings.
     *
     * @param QueryBuilder $queryBuilder
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
