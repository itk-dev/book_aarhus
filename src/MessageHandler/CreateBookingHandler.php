<?php

namespace App\MessageHandler;

use App\Entity\Resources\AAKResource;
use App\Message\CreateBookingMessage;
use App\Repository\Main\AAKResourceRepository;
use App\Service\MicrosoftGraphServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

/**
 * @see https://github.com/itk-dev/os2forms_selvbetjening/blob/develop/web/modules/custom/os2forms_rest_api/README.md
 */
#[AsMessageHandler]
class CreateBookingHandler
{
    public function __construct(private MicrosoftGraphServiceInterface $microsoftGraphService, private LoggerInterface $logger, private AAKResourceRepository $aakResourceRepository)
    {
    }

    public function __invoke(CreateBookingMessage $message): void
    {
        $this->logger->info('CreateBookingHandler invoked.');

        $booking = $message->getBooking();

        /** @var AAKResource $resource */
        $email = $booking->getResourceName();
        $resource = $this->aakResourceRepository->findOneBy(['resourceMail' => $email]);

        if (null == $resource) {
            throw new UnrecoverableMessageHandlingException("Resource $email not found.", 404);
        }

        if ($resource->isAcceptanceFlow()) {
            $this->microsoftGraphService->createBookingInviteResource(
                $booking->getResourceEmail(),
                $booking->getResourceName(),
                $booking->getSubject(),
                $booking->getBody(),
                $booking->getStartTime(),
                $booking->getEndTime(),
            );
        } else {
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
}
