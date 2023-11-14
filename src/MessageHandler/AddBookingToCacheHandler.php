<?php

namespace App\MessageHandler;

use App\Message\AddBookingToCacheMessage;
use App\Service\BookingServiceInterface;
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
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(AddBookingToCacheMessage $message): void
    {
        $this->logger->info('AddBookingToCacheHandler invoked.');

        $booking = $message->getBooking();
        $id = $this->bookingService->getBookingIdFromICalUid($message->getICalUID()) ?? null;

        if (null != $id) {
            $this->userBookingCacheService->addCacheEntryFromArray([
                'subject' => $booking->getSubject(),
                'id' => $id,
                'body' => $booking->getBody(),
                'start' => $booking->getStartTime(),
                'end' => $booking->getEndTime(),
                'status' => 'AWAITING_APPROVAL',
                'resourceMail' => $booking->getResourceEmail(),
            ]);
        } else {
            throw new RecoverableMessageHandlingException(sprintf('Booking id could not be retrieved for booking with iCalUID: %s', $message->getICalUID()));
        }
    }
}
