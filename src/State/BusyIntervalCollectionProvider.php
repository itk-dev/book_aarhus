<?php

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Main\BusyInterval;
use App\Exception\MicrosoftGraphCommunicationException;
use App\Service\BookingServiceInterface;
use App\Service\MetricsHelper;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Uid\Ulid;

/**
 * @template-implements ProviderInterface<object>
 */
class BusyIntervalCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
        private readonly MetricsHelper $metricsHelper,
    ) {
    }

    public function supports(string $resourceClass): bool
    {
        return BusyInterval::class === $resourceClass;
    }

    /**
     * @throws MicrosoftGraphCommunicationException
     * @throws \Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $result = [];

        if ($operation instanceof CollectionOperationInterface) {
            if (!isset($context['filters'])) {
                $this->metricsHelper->incExceptionTotal(BadRequestHttpException::class);
                $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::EXCEPTION);
                throw new BadRequestHttpException('Required filters are not set');
            }

            $filters = $context['filters'];

            if (!isset($filters['dateStart'])) {
                $this->metricsHelper->incExceptionTotal(BadRequestHttpException::class);
                $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::EXCEPTION);
                throw new BadRequestHttpException('Required dateStart filters not set');
            }

            if (!isset($filters['dateEnd'])) {
                $this->metricsHelper->incExceptionTotal(BadRequestHttpException::class);
                $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::EXCEPTION);
                throw new BadRequestHttpException('Required dateEnd filter not set.');
            }

            if (!isset($filters['resources'])) {
                $this->metricsHelper->incExceptionTotal(BadRequestHttpException::class);
                $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::EXCEPTION);
                throw new BadRequestHttpException('Required resources filter not set.');
            }

            $dateStart = new \DateTime($filters['dateStart']);
            $dateEnd = new \DateTime($filters['dateEnd']);
            $resources = explode(',', $filters['resources']);

            $busyIntervals = $this->bookingService->getBusyIntervals($resources, $dateStart, $dateEnd);

            foreach ($busyIntervals as $resourceName => $resourceEntry) {
                foreach ($resourceEntry as $entry) {
                    $busyInterval = new BusyInterval();
                    $busyInterval->resource = $resourceName;
                    $busyInterval->id = Ulid::generate();

                    $busyInterval->startTime = new \DateTime($entry['startTime']['dateTime'], new \DateTimeZone($entry['startTime']['timeZone']));
                    $busyInterval->endTime = new \DateTime($entry['endTime']['dateTime'], new \DateTimeZone($entry['endTime']['timeZone']));

                    $result[] = $busyInterval;
                }
            }
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return $result;
    }
}
