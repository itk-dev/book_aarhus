<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\BusyInterval;
use App\Service\MicrosoftGraphService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Uid\Ulid;

final class BusyIntervalCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(private MicrosoftGraphService $microsoftGraphService)
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
        $filters = $context['filters'];

        $dateStart = new \DateTime($filters['dateStart']);
        $dateEnd = new \DateTime($filters['dateEnd']);
        $resources = explode(',', $filters['resources']);

        if (empty($resources)) {
            throw new BadRequestHttpException();
        }

        $busyIntervals = $this->microsoftGraphService->getFreeBusy($resources, $dateStart, $dateEnd);

        foreach ($busyIntervals as $resourceName => $resourceEntry) {
            foreach ($resourceEntry as $entry) {
                $busyInterval = new BusyInterval();
                $busyInterval->resource = $resourceName;
                $busyInterval->id = Ulid::generate();

                $busyInterval->dateFrom = new \DateTime($entry['startTime']['dateTime'], new \DateTimeZone($entry['startTime']['timeZone']));
                $busyInterval->dateTo = new \DateTime($entry['endTime']['dateTime'], new \DateTimeZone($entry['endTime']['timeZone']));
                yield $busyInterval;
            }
        }
    }
}
