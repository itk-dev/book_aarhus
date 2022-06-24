<?php

namespace App\MessageHandler;

use App\Message\CreateBookingMessage;
use App\Service\MicrosoftGraphService;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @see https://github.com/itk-dev/os2forms_selvbetjening/blob/develop/web/modules/custom/os2forms_rest_api/README.md
 */
#[AsMessageHandler]
class CreateBookingHandler
{
    public function __construct(private MicrosoftGraphService $microsoftGraphService)
    {
    }

    /**
     * @throws GuzzleException|GraphException
     */
    public function __invoke(CreateBookingMessage $message): void
    {
        $booking = $message->getBooking();

        $this->microsoftGraphService->createBookingForResource(
            $booking->getResourceEmail(),
            $booking->getResourceName(),
            $booking->getSubject(),
            $booking->getBody(),
            $booking->getStartTime(),
            $booking->getEndTime(),
        );
    }
}
