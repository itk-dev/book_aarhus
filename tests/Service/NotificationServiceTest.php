<?php

namespace App\Tests\Service;

use App\Enum\NotificationTypeEnum;
use App\Interface\BookingServiceInterface;
use App\Service\MetricsHelper;
use App\Service\NotificationService;
use App\Service\ValidationUtils;
use App\Tests\AbstractBaseApiTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;

class NotificationServiceTest extends AbstractBaseApiTestCase
{
    public function testSendBookingConstructor(): void
    {
        $mailer = $this->createMock(MailerInterface::class);

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects($this->exactly(1))->method('warning');

        $validationUtils = self::getContainer()->get(ValidationUtils::class);

        $metric = $this->createMock(MetricsHelper::class);

        new NotificationService('from@example.com', 'error', $validationUtils, $logger, $mailer, 'Europe/Copenhagen', 'd/m/Y - H:i', $metric);
    }

    public function testSendBookingNotification(): void
    {
        $mailer = $this->createMock(MailerInterface::class);

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects($this->exactly(0))->method('warning');
        $logger->expects($this->exactly(0))->method('error');

        $validationUtils = self::getContainer()->get(ValidationUtils::class);

        $metric = $this->createMock(MetricsHelper::class);

        $notificationService = new NotificationService('from@example.com', 'admin@example.com', $validationUtils, $logger, $mailer, 'Europe/Copenhagen', 'd/m/Y - H:i', $metric);

        $booking = NotificationServiceData::getBooking();

        $resource = NotificationServiceData::getResource();

        $notificationService->sendBookingNotification($booking, $resource, NotificationTypeEnum::SUCCESS);

        $notificationService->sendBookingNotification($booking, $resource, NotificationTypeEnum::REQUEST_RECEIVED);

        $notificationService->sendBookingNotification($booking, $resource, NotificationTypeEnum::FAILED);

        $notificationService->sendBookingNotification($booking, $resource, NotificationTypeEnum::CONFLICT);
    }

    public function testNotifyAdmin(): void
    {
        $mailer = $this->createMock(MailerInterface::class);

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects($this->exactly(0))->method('warning');
        $logger->expects($this->exactly(0))->method('error');

        $validationUtils = self::getContainer()->get(ValidationUtils::class);

        $metric = $this->createMock(MetricsHelper::class);

        $notificationService = new NotificationService('from@example.com', 'admin@example.com', $validationUtils, $logger, $mailer, 'Europe/Copenhagen', 'd/m/Y - H:i', $metric);

        $booking = NotificationServiceData::getBooking();

        $resource = NotificationServiceData::getResource();

        $notificationService->notifyAdmin('subject', 'message', $booking, $resource);
    }

    public function testSendUserBookingNotification(): void
    {
        $mailer = $this->createMock(MailerInterface::class);

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects($this->exactly(0))->method('warning');
        $logger->expects($this->exactly(0))->method('error');

        $validationUtils = self::getContainer()->get(ValidationUtils::class);

        $metric = $this->createMock(MetricsHelper::class);

        $notificationService = new NotificationService('from@example.com', 'admin@example.com', $validationUtils, $logger, $mailer, 'Europe/Copenhagen', 'd/m/Y - H:i', $metric);

        $resource = NotificationServiceData::getResource();

        $userBookingData = MicrosoftGraphBookingServiceData::getUserBookingData1();

        $bookingService = self::getContainer()->get(BookingServiceInterface::class);

        $userBooking = $bookingService->getUserBookingFromApiData($userBookingData);

        $notificationService->sendUserBookingNotification($userBooking, $resource, NotificationTypeEnum::UPDATE_SUCCESS);

        $notificationService->sendUserBookingNotification($userBooking, $resource, NotificationTypeEnum::DELETE_SUCCESS);
    }
}
