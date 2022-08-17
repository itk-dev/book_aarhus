<?php

namespace App\MessageHandler;

use App\Message\CreateBookingMessage;
use App\Service\MicrosoftGraphServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @see https://github.com/itk-dev/os2forms_selvbetjening/blob/develop/web/modules/custom/os2forms_rest_api/README.md
 */
#[AsMessageHandler]
class CreateBookingHandler
{
    public function __construct(private MicrosoftGraphServiceInterface $microsoftGraphService, private LoggerInterface $logger)
    {
    }

    public function __invoke(CreateBookingMessage $message): void
    {
        $this->logger->info('CreateBookingHandler invoked.');

        $booking = $message->getBooking();

        $this->logger->info(implode('---', [
            $booking->getResourceEmail(),
            $booking->getResourceName(),
            $booking->getSubject(),
            $booking->getBody(),
            $booking->getStartTime(),
            $booking->getEndTime(),
        ]));
        /*
                $this->microsoftGraphService->createBookingForResource(
                    $booking->getResourceEmail(),
                    $booking->getResourceName(),
                    $booking->getSubject(),
                    $booking->getBody(),
                    $booking->getStartTime(),
                    $booking->getEndTime(),
                );
        */
    }
}
