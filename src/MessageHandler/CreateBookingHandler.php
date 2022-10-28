<?php

namespace App\MessageHandler;

use App\Entity\Resources\AAKResource;
use App\Enum\NotificationTypeEnum;
use App\Exception\BookingCreateException;
use App\Message\CreateBookingMessage;
use App\Message\SendBookingNotificationMessage;
use App\Repository\Main\AAKResourceRepository;
use App\Security\Voter\BookingVoter;
use App\Service\BookingServiceInterface;
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
        private readonly BookingServiceInterface $bookingService,
        private readonly LoggerInterface $logger,
        private readonly AAKResourceRepository $aakResourceRepository,
        private readonly Security $security,
        private readonly MessageBusInterface $bus
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
                $this->bookingService->createBookingInviteResource(
                    $booking->getResourceEmail(),
                    $booking->getResourceName(),
                    $booking->getSubject(),
                    $booking->getBody(),
                    $booking->getStartTime(),
                    $booking->getEndTime(),
                );
            } else {
                $this->bookingService->createBookingForResource(
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
                    NotificationTypeEnum::SUCCESS
                ));
            }
        } catch (\Exception $exception) {
            // Differentiate between errors:
            // If it is a BookingCreateException it should be rejected otherwise it should be retried.
            if ($exception instanceof BookingCreateException) {
                throw new UnrecoverableMessageHandlingException($exception->getMessage(), (int) $exception->getCode());
            } else {
                throw $exception;
            }
        }
    }
}
