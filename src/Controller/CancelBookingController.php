<?php

namespace App\Controller;

use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use App\Entity\Main\ApiKeyUser;
use App\Entity\Main\UserBooking;
use App\Enum\NotificationTypeEnum;
use App\Exception\MicrosoftGraphCommunicationException;
use App\Exception\UserBookingException;
use App\Message\RemoveBookingFromCacheMessage;
use App\Message\SendUserBookingNotificationMessage;
use App\Repository\Resources\AAKResourceRepository;
use App\Security\Voter\UserBookingVoter;
use App\Service\BookingServiceInterface;
use App\Service\CreateBookingService;
use App\Service\MetricsHelper;
use App\Service\UserBookingCacheServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[AsController]
class CancelBookingController extends AbstractController
{
    public function __construct(
        private readonly MetricsHelper $metricsHelper,
        private readonly BookingServiceInterface $bookingService,
        private readonly UserBookingCacheServiceInterface $userBookingCacheService,
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        // Algorithm:
        // Validate input.
        // Cancel bookings.

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $content = $request->toArray();

        $ids = $content['ids'] ?? false;

        if (empty($ids)) {
            throw new InvalidArgumentException('No ids provided.');
        }

        try {
            foreach ($ids as $id) {

                // TODO: Validate that user is allowed to delete booking.

                $userBooking = $this->entityManager->getRepository(UserBooking::class)->findOneBy(['iiCalUId' => $id]);
                if (!$userBooking) {
                    throw new \Exception("No user booking found for iCalUId: $id");
                }

                if (!$this->security->isGranted(UserBookingVoter::DELETE, $userBooking)) {
                    $this->metricsHelper->incExceptionTotal(AccessDeniedHttpException::class);
                    throw new AccessDeniedHttpException('Access denied');
                }

                $this->bookingService->deleteBookingByICalUid($id);
                $this->userBookingCacheService->deleteCacheEntryByICalUId($id);

            }
        } catch (\Throwable $e) {
            $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::EXCEPTION);
            // TODO: Should we respond which has failed?
            throw $e;
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return new Response(null, 200);
    }
}
