<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Main\UserBookingCacheEntry;
use App\Service\UserBookingCacheServiceInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class UserBookingCacheEntryCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
  public function __construct(
    private readonly UserBookingCacheServiceInterface $bookingCacheService,
    private readonly RequestStack $requestStack,
  ) {
  }

  public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
  {
    return UserBookingCacheEntry::class === $resourceClass;
  }

  /**
   * @throws \Exception
   */
  public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
  {
    $request = $this->requestStack->getCurrentRequest();

    if (is_null($request)) {
      throw new BadRequestHttpException('Request not set.');
    }
    /*


    $userId = $request->headers->get('Authorization-UserId') ?? null;

    if (is_null($userId)) {
      throw new BadRequestHttpException('Required Authorization-UserId header is not set.');
    }
*/
    $page = intval($request->query->get('page'));

    $pageSize = intval($request->query->get('pageSize'));
    if (0 === $pageSize) {
      $pageSize = 25;
    }

    // @todo change $userId parameter to variable
    return $this->bookingCacheService->getUserCachedBookings('QFrRoMU35ilLCJbE3XR8VSNoJsTe-B_P3oJ-SlstNpE', $context['filters'], $page, $pageSize);
  }
}
