<?php

namespace App\MessageHandler;

use App\Entity\Main\Resource;
use App\Enum\UserBookingStatusEnum;
use App\Interface\BookingServiceInterface;
use App\Interface\UserBookingCacheServiceInterface;
use App\Message\AddBookingToCacheMessage;
use App\Repository\ResourceRepository;
use App\Service\MetricsHelper;
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
        private readonly ResourceRepository $resourceRepository,
        private readonly MetricsHelper $metricsHelper,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(AddBookingToCacheMessage $message): void
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $id = $this->bookingService->getBookingIdFromICalUid($message->getICalUID()) ?? null;

        if (null != $id) {
            $booking = $message->getBooking();

            $resourceEmail = $booking->getResourceEmail();
            $resourceDisplayName = $booking->getResourceName();

            /** @var Resource $resource */
            $resource = $this->resourceRepository->findOneBy(['resourceMail' => $resourceEmail]);

            if (!empty($resource?->getResourceDisplayName())) {
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
            $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::EXCEPTION);
            $this->metricsHelper->incExceptionTotal(RecoverableMessageHandlingException::class);

            throw new RecoverableMessageHandlingException(sprintf('Booking id could not be retrieved for booking with iCalUID: %s', $message->getICalUID()));
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);
    }
}
