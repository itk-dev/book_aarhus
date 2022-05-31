<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\BusyInterval;
use App\Service\MicrosoftGraphServiceInterface;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Uid\Ulid;

final class BusyIntervalCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(private MicrosoftGraphServiceInterface $microsoftGraphService)
    {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return BusyInterval::class === $resourceClass;
    }

    /**
     * @throws GuzzleException
     * @throws GraphException
     * @throws Exception
     */
    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        if (!isset($context['filters'])) {
            throw new BadRequestHttpException('Required filters not set.');
        }

        $filters = $context['filters'];

        if (!isset($filters['dateStart'])) {
            throw new BadRequestHttpException('Required dateStart filter not set.');
        }

        if (!isset($filters['dateEnd'])) {
            throw new BadRequestHttpException('Required dateEnd filter not set.');
        }

        if (!isset($filters['resources'])) {
            throw new BadRequestHttpException('Required resources filter not set.');
        }

        $dateStart = new \DateTime($filters['dateStart']);
        $dateEnd = new \DateTime($filters['dateEnd']);
        $resources = explode(',', $filters['resources']);

        $busyIntervals = $this->microsoftGraphService->getFreeBusy($resources, $dateStart, $dateEnd);

        foreach ($busyIntervals as $resourceName => $resourceEntry) {
            foreach ($resourceEntry as $entry) {
                $busyInterval = new BusyInterval();
                $busyInterval->resource = $resourceName;
                $busyInterval->id = Ulid::generate();

                $busyInterval->startTime = new \DateTime($entry['startTime']['dateTime'], new \DateTimeZone($entry['startTime']['timeZone']));
                $busyInterval->endTime = new \DateTime($entry['endTime']['dateTime'], new \DateTimeZone($entry['endTime']['timeZone']));
                yield $busyInterval;
            }
        }
    }
}
