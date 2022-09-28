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
        $email = $booking->getResourceEmail();
        $resource = $this->aakResourceRepository->findOneByEmail($email);

        if (null == $resource) {
            throw new UnrecoverableMessageHandlingException("Resource $email not found.", 404);
        }

        try {
            if ($resource->isAcceptanceFlow()) {
                $this->microsoftGraphService->createBookingInviteResource(
                    $booking->getResourceEmail(),
                    $booking->getResourceName(),
                    $booking->getSubject(),
                    $booking->getBody(),
                    $booking->getStartTime(),
                    $booking->getEndTime(),
                );
                // TODO: Send booking "sent to acceptance" notification.
            } else {
                $this->microsoftGraphService->createBookingForResource(
                    $booking->getResourceEmail(),
                    $booking->getResourceName(),
                    $booking->getSubject(),
                    $booking->getBody(),
                    $booking->getStartTime(),
                    $booking->getEndTime(),
                );
                // TODO: Send booking success notification.
            }
        } catch (\Exception $exception) {
            // TODO: Send booking failed notification.
        }
    }
}
