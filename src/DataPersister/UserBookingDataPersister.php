<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Main\UserBooking;
use App\Enum\NotificationTypeEnum;
use App\Exception\MicrosoftGraphCommunicationException;
use App\Exception\UserBookingException;
use App\Message\RemoveBookingFromCacheMessage;
use App\Message\SendUserBookingNotificationMessage;
use App\Message\UpdateBookingInCacheMessage;
use App\Security\Voter\UserBookingVoter;
use App\Service\BookingServiceInterface;
use App\Service\MetricsHelper;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\MessageBusInterface;

class UserBookingDataPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
        private readonly Security $security,
        private readonly MessageBusInterface $bus,
        private readonly MetricsHelper $metricsHelper,
    ) {
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof UserBooking;
    }

    public function remove($data, array $context = []): void
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        try {
            if ($data instanceof UserBooking) {
                if (!$this->security->isGranted(UserBookingVoter::DELETE, $data)) {
                    throw new AccessDeniedHttpException('Access denied');
                }

                $this->bookingService->deleteBooking($data);

                $this->bus->dispatch(new SendUserBookingNotificationMessage(
                    $data,
                    NotificationTypeEnum::DELETE_SUCCESS
                ));

                $this->bus->dispatch(new RemoveBookingFromCacheMessage(
                    $data->id
                ));
            }
        } catch (MicrosoftGraphCommunicationException|UserBookingException $e) {
            throw new HttpException($e->getCode(), 'Booking could not be deleted.');
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);
    }

    public function persist($data, array $context = []): mixed
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        try {
            if ($data instanceof UserBooking) {
                if (!$this->security->isGranted(UserBookingVoter::EDIT, $data)) {
                    throw new AccessDeniedHttpException('Access denied');
                }

                $this->bookingService->updateBooking($data);

                $this->bus->dispatch(new SendUserBookingNotificationMessage(
                    $data,
                    NotificationTypeEnum::UPDATE_SUCCESS
                ));

                $this->bus->dispatch(new UpdateBookingInCacheMessage(
                    $data->id,
                    [
                        'start' => $data->start,
                        'end' => $data->end,
                    ],
                ));
            }

            $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

            return $data;
        } catch (MicrosoftGraphCommunicationException|UserBookingException $e) {
            throw new HttpException($e->getCode(), 'Booking could not be updated.');
        }
    }
}
