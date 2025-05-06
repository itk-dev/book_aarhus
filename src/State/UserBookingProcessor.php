<?php

namespace App\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Api\UserBooking;
use App\Enum\NotificationTypeEnum;
use App\Exception\MicrosoftGraphCommunicationException;
use App\Exception\UserBookingException;
use App\Interface\BookingServiceInterface;
use App\Message\RemoveBookingFromCacheMessage;
use App\Message\SendUserBookingNotificationMessage;
use App\Message\UpdateBookingInCacheMessage;
use App\Security\Voter\UserBookingVoter;
use App\Service\MetricsHelper;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @template-implements ProcessorInterface<UserBooking, UserBooking>
 */
class UserBookingProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
        private readonly Security $security,
        private readonly MessageBusInterface $bus,
        private readonly MetricsHelper $metricsHelper,
    ) {
    }

    public function supports($data): bool
    {
        return $data instanceof UserBooking;
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        if ($operation instanceof DeleteOperationInterface) {
            $this->metricsHelper->incMethodTotal(__METHOD__, 'delete');

            try {
                if ($data instanceof UserBooking) {
                    if (!$this->security->isGranted(UserBookingVoter::DELETE, $data)) {
                        $this->metricsHelper->incExceptionTotal(AccessDeniedHttpException::class);
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
        } else {
            $this->metricsHelper->incMethodTotal(__METHOD__, 'edit');

            try {
                if ($data instanceof UserBooking) {
                    if (!$this->security->isGranted(UserBookingVoter::EDIT, $data)) {
                        $this->metricsHelper->incExceptionTotal(AccessDeniedHttpException::class);
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
            } catch (MicrosoftGraphCommunicationException|UserBookingException $e) {
                throw new HttpException($e->getCode(), 'Booking could not be updated.');
            }
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return $data;
    }
}
