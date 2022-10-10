<?php

namespace App\MessageHandler;

use App\Entity\Resources\AAKResource;
use App\Message\CreateBookingMessage;
use App\Message\SendBookingNotificationMessage;
use App\Repository\Main\AAKResourceRepository;
use App\Security\Voter\BookingVoter;
use App\Service\MicrosoftGraphServiceInterface;
use App\Service\NotificationServiceInterface;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @see https://github.com/itk-dev/os2forms_selvbetjening/blob/develop/web/modules/custom/os2forms_rest_api/README.md
 */
#[AsMessageHandler]
class CreateBookingHandler
{
    public function __construct(
        private MicrosoftGraphServiceInterface $microsoftGraphService,
        private LoggerInterface $logger,
        private AAKResourceRepository $aakResourceRepository,
        private Security $security,
        private MessageBusInterface $bus
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(CreateBookingMessage $message): void
    {
        $this->logger->info('CreateBookingHandler invoked.');

        $booking = $message->getBooking();

        if (!$this->security->isGranted(BookingVoter::CREATE, $booking)) {
            throw new UnrecoverableMessageHandlingException('User does not have permission to create bookings for the given resource.', 403);
        }

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
            } else {
                $this->microsoftGraphService->createBookingForResource(
                    $booking->getResourceEmail(),
                    $booking->getResourceName(),
                    $booking->getSubject(),
                    $booking->getBody(),
                    $booking->getStartTime(),
                    $booking->getEndTime(),
                );

                // Register notification job.
                $this->bus->dispatch(new SendBookingNotificationMessage(
                    $booking,
                    NotificationServiceInterface::BOOKING_TYPE_SUCCESS
                ));
            }
        } catch (\Exception $exception) {
            $exceptionCode = (int) $exception->getCode();

            // Differentiate between errors:
            // If it is a conflict it should be rejected.
            // If guzzle error it is Graph related and should be retried.
            // If the booking has not been found after response from Graph, it should be retried.
            if (in_array($exceptionCode, [409, 404]) || !($exception instanceof GuzzleException) && !($exception instanceof GraphException)) {
                throw new UnrecoverableMessageHandlingException($exception->getMessage(), $exceptionCode);
            } else {
                throw $exception;
            }
        }
    }
}
