<?php

namespace App\Tests\Handler;

use App\Entity\Main\ApiKeyUser;
use App\Entity\Main\Booking;
use App\Entity\Resources\AAKResource;
use App\Message\AddBookingToCacheMessage;
use App\Message\CreateBookingMessage;
use App\Message\WebformSubmitMessage;
use App\MessageHandler\AddBookingToCacheHandler;
use App\MessageHandler\CreateBookingHandler;
use App\MessageHandler\WebformSubmitHandler;
use App\Repository\Resources\AAKResourceRepository;
use App\Repository\Resources\CvrWhitelistRepository;
use App\Security\Voter\BookingVoter;
use App\Service\BookingServiceInterface;
use App\Service\MicrosoftGraphBookingService;
use App\Service\NotificationServiceInterface;
use App\Service\UserBookingCacheServiceInterface;
use App\Service\WebformService;
use App\Tests\AbstractBaseApiTestCase;
use App\Utils\ValidationUtils;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Twig\Environment;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class AddBookingToCacheHandlerTest extends AbstractBaseApiTestCase
{
    public function testHandlerVoter(): void
    {
        $bookingServiceMock = $this->getMockBuilder(BookingServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $bookingServiceMock->expects($this->exactly(1))->method('getBookingIdFromICalUid')->willReturn("abcde");

        $userBookingCacheServiceMock = $this->getMockBuilder(UserBookingCacheServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userBookingCacheServiceMock->expects($this->exactly(1))->method('addCacheEntryFromArray');

        $loggerMock = $this->createMock(LoggerInterface::class);

        $handler = new AddBookingToCacheHandler(
            $bookingServiceMock,
            $userBookingCacheServiceMock,
            $loggerMock,
        );

        $booking = new Booking();
        $booking->setBody('test');
        $booking->setSubject('test');
        $booking->setResourceName('test');
        $booking->setResourceEmail('test@bookaarhus.local.itkdev.dk');
        $booking->setStartTime(new \DateTime());
        $booking->setEndTime(new \DateTime());
        $booking->setUserName('author1');
        $booking->setUserMail('author1@bookaarhus.local.itkdev.dk');
        $booking->setMetaData([
            'meta_data_4' => '1, 2, 3',
            'meta_data_5' => 'a1, b2, c3',
            'meta_data_1' => 'This is a metadata field',
            'meta_data_2' => 'This is also metadata',
            'meta_data_3' => 'Lorem ipsum metadata',
        ]);
        $booking->setUserPermission(BookingVoter::PERMISSION_CITIZEN);
        $booking->setUserId('1234567890');

        $message = new AddBookingToCacheMessage(
            $booking,
            "12345"
        );

        $handler->__invoke($message);
    }
}
