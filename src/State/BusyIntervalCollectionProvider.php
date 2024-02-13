<?php

namespace App\State;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use App\Entity\Main\BusyInterval;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Exception\MicrosoftGraphCommunicationException;
use App\Service\BookingServiceInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Uid\Ulid;
use function PHPUnit\Framework\throwException;

final class BusyIntervalCollectionProvider implements ProviderInterface
//final class BusyIntervalCollectionProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{

    public function __construct(private readonly BookingServiceInterface $bookingService)
    {

    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return BusyInterval::class === $resourceClass;
    }




//      /**
//       * {@inheritDoc}
//       * @throws MicrosoftGraphCommunicationException
//       */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {

        if( $operation instanceof CollectionOperationInterface){

            if(!isset($context['filters'])){
                throw new BadRequestHttpException('Required filters are not set');
            }

            $filters = $context['filters'];

            if(!isset($filters['dateStart'])){
                throw new BadRequestHttpException('Required dateStart filters not set');
            }

            if (!isset($filters['dateEnd'])) {
                throw new BadRequestHttpException('Required dateEnd filter not set.');
            }

            if (!isset($filters['resources'])) {
                throw new BadRequestHttpException('Required resources filter not set.');
            }



            $dateStart = new \DateTime($filters['dateStart']);
            $dateEnd = new \DateTime($filters['dateStart']);
            $resources =  explode(',', $filters['resources']);


            $busyIntervals = $this->bookingService->getBusyIntervals($resources, $dateStart, $dateEnd);

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

//        throw new BadRequestHttpException('Bad Request !');



    }

}