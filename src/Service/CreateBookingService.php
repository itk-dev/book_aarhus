<?php

namespace App\Service;

use App\Entity\Main\Booking;
use App\Entity\Resources\AAKResource;
use App\Enum\NotificationTypeEnum;
use App\Exception\BookingCreateConflictException;
use App\Message\AddBookingToCacheMessage;
use App\Message\SendBookingNotificationMessage;
use App\Repository\Resources\AAKResourceRepository;
use App\Repository\Resources\CvrWhitelistRepository;
use App\Security\Voter\BookingVoter;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class CreateBookingService
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
        private readonly LoggerInterface $logger,
        private readonly AAKResourceRepository $aakResourceRepository,
        private readonly Security $security,
        private readonly MessageBusInterface $bus,
        private readonly CvrWhitelistRepository $whitelistRepository,
        private readonly MetricsHelper $metricsHelper,
    ) {
    }

    public function createBooking(Booking $booking): void
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        if (!$this->security->isGranted(BookingVoter::CREATE, $booking)) {
            $this->logger->error('User does not have permission to create bookings for the given resource.');
            throw new AccessDeniedException('User does not have permission to create bookings for the given resource.');
        }

        /** @var AAKResource $resource */
        $email = $booking->getResourceEmail();
        $resource = $this->aakResourceRepository->findOneByEmail($email);

        if (null == $resource) {
            $this->logger->error("Resource $email not found.");
            throw new NotFoundHttpException("Resource $email not found.");
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
            }

            if (isset($response['iCalUId'])) {
                $message = new AddBookingToCacheMessage(
                    $booking,
                    $response['iCalUId'],
                );

                $envelope = new Envelope($message, [
                    new DelayStamp(5000),
                ]);

                $this->bus->dispatch($envelope);
            } else {
                $this->logger->error(sprintf('Booking iCalUID could not be retrieved for booking with subject: %s', $booking->getSubject()));
                $this->metricsHelper->incMethodTotal(__METHOD__, 'icaluid_not_found');
            }
        } catch (BookingCreateConflictException $exception) {
            // If it is a BookingCreateConflictException the booking should be rejected.
            $this->logger->notice(sprintf('Booking conflict detected: %d %s', $exception->getCode(), $exception->getMessage()));
            $this->metricsHelper->incExceptionTotal(BookingCreateConflictException::class);
            $this->metricsHelper->incMethodTotal(__METHOD__, 'booking_conflict_detected');
        } catch (\Exception $exception) {
            // Other exceptions should logged, then re-thrown for the message to be re-queued.
            $this->logger->error(sprintf('CreateBookingHandler exception: %d %s', $exception->getCode(), $exception->getMessage()));

            $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::EXCEPTION);
            $this->metricsHelper->incExceptionTotal(\Exception::class);

            throw $exception;
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);
    }
}
