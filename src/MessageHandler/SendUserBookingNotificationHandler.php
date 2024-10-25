<?php

namespace App\MessageHandler;

use App\Entity\Resources\AAKResource;
use App\Exception\NoNotificationReceiverException;
use App\Exception\UnsupportedNotificationTypeException;
use App\Message\SendUserBookingNotificationMessage;
use App\Repository\Resources\AAKResourceRepository;
use App\Service\MetricsHelper;
use App\Service\NotificationServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class SendUserBookingNotificationHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly AAKResourceRepository $aakResourceRepository,
        private readonly NotificationServiceInterface $notificationService,
        private readonly MetricsHelper $metricsHelper,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function __invoke(SendUserBookingNotificationMessage $message): void
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        try {
            $this->logger->info('SendBookingNotificationHandler.');

            $userBooking = $message->getUserBooking();
            $type = $message->getType();

            /** @var AAKResource $resource */
            $email = $userBooking->resourceMail;
            $resource = $this->aakResourceRepository->findOneByEmail($email);

            $this->notificationService->sendUserBookingNotification($userBooking, $resource, $type);
        } catch (NoNotificationReceiverException|UnsupportedNotificationTypeException $e) {
            $this->logger->error(sprintf('SendUserBookingNotificationHandler exception: %d %s', $e->getCode(), $e->getMessage()));
            $this->metricsHelper->incExceptionTotal(UnrecoverableMessageHandlingException::class);

            throw new UnrecoverableMessageHandlingException($e->getMessage(), $e->getCode(), $e);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(sprintf('SendUserBookingNotificationHandler exception: %d %s', $e->getCode(), $e->getMessage()));
            $this->metricsHelper->incExceptionTotal(TransportExceptionInterface::class);

            throw $e;
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);
    }
}
