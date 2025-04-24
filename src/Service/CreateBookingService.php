<?php

namespace App\Service;

use ApiPlatform\Symfony\Security\Exception\AccessDeniedException;
use App\Entity\Main\Booking;
use App\Entity\Resources\AAKResource;
use App\Enum\UserBookingStatusEnum;
use App\Exception\BookingContentsException;
use App\Exception\BookingCreateConflictException;
use App\Repository\Resources\AAKResourceRepository;
use App\Repository\Resources\CvrWhitelistRepository;
use App\Security\Voter\BookingVoter;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class CreateBookingService
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
        private readonly LoggerInterface $logger,
        private readonly AAKResourceRepository $aakResourceRepository,
        private readonly Security $security,
        private readonly CvrWhitelistRepository $whitelistRepository,
        private readonly MetricsHelper $metricsHelper,
        private readonly UserBookingCacheServiceInterface $userBookingCacheService,
        private readonly AAKResourceRepository $resourceRepository,
        private readonly Environment $twig,
    ) {
    }

    public function createBooking(Booking $booking): array
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

                $status = UserBookingStatusEnum::AWAITING_APPROVAL->name;
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

                $status = UserBookingStatusEnum::ACCEPTED->name;
            }

            $iCalUId = $response['iCalUId'];
            $this->addBookingToCache($booking, $iCalUId, $status);

            // TODO: Figure out which ID should be passed around.
            return [
                'id' => $booking->getId(),
                'iCalUId' => $iCalUId,
                'status' => $status,
            ];
        } catch (BookingCreateConflictException $exception) {
            // If it is a BookingCreateConflictException the booking should be rejected.
            $this->logger->notice(sprintf('Booking conflict detected: %d %s', $exception->getCode(), $exception->getMessage()));
            $this->metricsHelper->incExceptionTotal(BookingCreateConflictException::class);
            $this->metricsHelper->incMethodTotal(__METHOD__, 'booking_conflict_detected');
        } catch (\Exception $exception) {
            // Other exceptions should be logged, then re-thrown for the message to be re-queued.
            $this->logger->error(sprintf('CreateBookingService exception: %d %s', $exception->getCode(), $exception->getMessage()));

            $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::EXCEPTION);
            $this->metricsHelper->incExceptionTotal(\Exception::class);
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        // TODO: Figure out which ID should be passed around.
        return ['id' => $booking->getId(), 'status' => UserBookingStatusEnum::DECLINED->name];
    }

    public function addBookingToCache(Booking $booking, string $iCalUId, string $status): void
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $resourceEmail = $booking->getResourceEmail();
        $resourceDisplayName = $booking->getResourceName();

        /** @var AAKResource $resource */
        $resource = $this->resourceRepository->findOneBy(['resourceMail' => $resourceEmail]);

        if (null != $resource && $resource->getResourceDisplayName()) {
            $resourceDisplayName = $resource->getResourceDisplayName();
        }

        $this->userBookingCacheService->addCacheEntryFromArray([
            'subject' => $booking->getSubject(),
            'id' => '', // exchange_id, which is now replaced by iCalUId.
            'iCalUId' => $iCalUId,
            'body' => $booking->getBody(),
            'start' => $booking->getStartTime(),
            'end' => $booking->getEndTime(),
            'status' => $status,
            'resourceMail' => $booking->getResourceEmail(),
            'resourceDisplayName' => $resourceDisplayName,
        ]);

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);
    }

    /**
     * @throws BookingContentsException
     */
    public function composeBookingContents($data, AAKResource $resource, $metaData): array
    {
        try {
            $body = [];
            $body['resource'] = $resource;
            $body['submission'] = $data;
            $body['submission']['fromObj'] = new \DateTime($data['start']);
            $body['submission']['toObj'] = new \DateTime($data['end']);
            $body['metaData'] = $metaData;
            $body['userUniqueId'] = $this->bookingService->createBodyUserId($data['userId']);

            return $body;
        } catch (\Exception $exception) {
            $this->metricsHelper->incExceptionTotal(\Exception::class);

            throw new BookingContentsException($exception->getMessage());
        }
    }

    /**
     * @throws BookingContentsException
     */
    public function renderContentsAsHtml(array $body): string
    {
        try {
            return $this->twig->render('booking.html.twig', $body);
        } catch (RuntimeError|SyntaxError|LoaderError $error) {
            $this->metricsHelper->incExceptionTotal(\Error::class);

            throw new BookingContentsException($error->getMessage());
        }
    }
}
