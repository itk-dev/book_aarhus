<?php

namespace App\MessageHandler;

use App\Entity\Resources\AAKResource;
use App\Enum\UserBookingStatusEnum;
use App\Message\AddBookingToCacheMessage;
use App\Repository\Resources\AAKResourceRepository;
use App\Service\BookingServiceInterface;
use App\Service\Metric;
use App\Service\UserBookingCacheServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;

/**
 * @see https://github.com/itk-dev/os2forms_selvbetjening/blob/develop/web/modules/custom/os2forms_rest_api/README.md
 */
#[AsMessageHandler]
class AddBookingToCacheHandler
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
        private readonly UserBookingCacheServiceInterface $userBookingCacheService,
        private readonly AAKResourceRepository $resourceRepository,
        private readonly Metric $metric,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(AddBookingToCacheMessage $message): void
    {
        $this->metric->incFunctionTotal($this, __FUNCTION__, Metric::INVOKE);

        $id = $this->bookingService->getBookingIdFromICalUid($message->getICalUID()) ?? null;

        if (null != $id) {
            $booking = $message->getBooking();

            $resourceEmail = $booking->getResourceEmail();
            $resourceDisplayName = $booking->getResourceName();

            /** @var AAKResource $resource */
            $resource = $this->resourceRepository->findOneBy(['resourceMail' => $resourceEmail]);

            if (null != $resource && $resource->getResourceDisplayName()) {
                $resourceDisplayName = $resource->getResourceDisplayName();
            }

            $this->userBookingCacheService->addCacheEntryFromArray([
                'subject' => $booking->getSubject(),
                'id' => $id,
                'body' => $booking->getBody(),
                'start' => $booking->getStartTime(),
                'end' => $booking->getEndTime(),
                'status' => UserBookingStatusEnum::AWAITING_APPROVAL->name,
                'resourceMail' => $booking->getResourceEmail(),
                'resourceDisplayName' => $resourceDisplayName,
            ]);
        } else {
            $this->metric->incExceptionTotal(RecoverableMessageHandlingException::class);

            throw new RecoverableMessageHandlingException(sprintf('Booking id could not be retrieved for booking with iCalUID: %s', $message->getICalUID()));
        }

        $this->metric->incFunctionTotal($this, __FUNCTION__, Metric::COMPLETE);
    }
}
