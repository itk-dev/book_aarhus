<?php

namespace App\MessageHandler;

use App\Entity\Resources\AAKResource;
use App\Enum\NotificationTypeEnum;
use App\Exception\BookingCreateConflictException;
use App\Message\AddBookingToCacheMessage;
use App\Message\CreateBookingMessage;
use App\Message\SendBookingNotificationMessage;
use App\Repository\Resources\AAKResourceRepository;
use App\Repository\Resources\CvrWhitelistRepository;
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
        private readonly MessageBusInterface $bus,
        private readonly CvrWhitelistRepository $whitelistRepository,
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
            $this->logger->error('User does not have permission to create bookings for the given resource.');

            throw new UnrecoverableMessageHandlingException('User does not have permission to create bookings for the given resource.', 403);
        }

        /** @var AAKResource $resource */
        $email = $booking->getResourceEmail();
        $resource = $this->aakResourceRepository->findOneByEmail($email);

        if (null == $resource) {
            $this->logger->error("Resource $email not found.");

            throw new UnrecoverableMessageHandlingException("Resource $email not found.", 404);
        }

        $acceptanceFlow = $resource->isAcceptanceFlow();

        // If the user is whitelisted to the resource the booking should be an instant booking even though the
        // resource is set to acceptanceFlow.
        if ($acceptanceFlow) {
            if ($resource->getHasWhitelist()) {
                $whitelistKey = $booking->getWhitelistKey();

                if (null !== $whitelistKey) {
                    $whitelistEntries = $this->whitelistRepository->findBy(['resourceId' => $resource->getId(), 'cvr' => $whitelistKey]);

                    if (count($whitelistEntries) > 0) {
                        $acceptanceFlow = false;
                    }
                }
            }
        }

        $acceptConflict = true == $resource->getAcceptConflict();

        try {
            if ($acceptanceFlow) {
                $response = $this->bookingService->createBookingInviteResource(
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
                    NotificationTypeEnum::REQUEST_RECEIVED
                ));
            } else {
                $response = $this->bookingService->createBookingForResource(
                    $booking->getResourceEmail(),
                    $booking->getResourceName(),
                    $booking->getSubject(),
                    $booking->getBody(),
                    $booking->getStartTime(),
                    $booking->getEndTime(),
                    $acceptConflict,
                );

                // Register notification job.
                $this->bus->dispatch(new SendBookingNotificationMessage(
                    $booking,
                    NotificationTypeEnum::SUCCESS
                ));
            }

            if (isset($response['iCalUId'])) {
                $this->bus->dispatch(new AddBookingToCacheMessage(
                    $booking,
                    $response['iCalUId'],
                ));
            } else {
                $this->logger->error(sprintf('Booking iCalUID could not be retrieved for booking with subject: %s', $booking->getSubject()));
            }
        } catch (BookingCreateConflictException $exception) {
            // If it is a BookingCreateConflictException the booking should be rejected.
            $this->logger->notice(sprintf('Booking conflict detected: %d %s', $exception->getCode(), $exception->getMessage()));

            $this->bus->dispatch(new SendBookingNotificationMessage(
                $booking,
                NotificationTypeEnum::CONFLICT
            ));
        } catch (\Exception $exception) {
            // Other exceptions should logged, then re-thrown for the message to be re-queued.
            $this->logger->error(sprintf('CreateBookingHandler exception: %d %s', $exception->getCode(), $exception->getMessage()));

            throw $exception;
        }
    }
}
