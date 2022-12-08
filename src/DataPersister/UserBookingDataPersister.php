<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Main\UserBooking;
use App\Enum\NotificationTypeEnum;
use App\Exception\MicrosoftGraphCommunicationException;
use App\Exception\UserBookingException;
use App\Message\SendUserBookingNotificationMessage;
use App\Security\Voter\UserBookingVoter;
use App\Service\BookingServiceInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

class UserBookingDataPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
        private readonly Security $security,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof UserBooking;
    }

    public function remove($data, array $context = [])
    {
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
            }
        } catch (MicrosoftGraphCommunicationException|UserBookingException $e) {
            throw new HttpException($e->getCode(), 'Booking could not be deleted.');
        }
    }

    public function persist($data, array $context = [])
    {
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
            }
        } catch (MicrosoftGraphCommunicationException|UserBookingException $e) {
            throw new HttpException($e->getCode(), 'Booking could not be updated.');
        }

        return $data;
    }
}
