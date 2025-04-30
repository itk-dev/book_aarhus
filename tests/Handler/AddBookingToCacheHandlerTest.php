<?php

namespace App\Tests\Handler;

use App\Entity\Main\Booking;
use App\Entity\Main\Resource;
use App\Interface\BookingServiceInterface;
use App\Interface\UserBookingCacheServiceInterface;
use App\Message\AddBookingToCacheMessage;
use App\MessageHandler\AddBookingToCacheHandler;
use App\Repository\ResourceRepository;
use App\Security\Voter\BookingVoter;
use App\Service\MetricsHelper;
use App\Tests\AbstractBaseApiTestCase;

class AddBookingToCacheHandlerTest extends AbstractBaseApiTestCase
{
    public function testHandlerVoter(): void
    {
        $bookingServiceMock = $this->getMockBuilder(BookingServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $bookingServiceMock->expects($this->exactly(1))->method('getBookingIdFromICalUid')->willReturn('abcde');

        $userBookingCacheServiceMock = $this->getMockBuilder(UserBookingCacheServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userBookingCacheServiceMock->expects($this->exactly(1))->method('addCacheEntryFromArray');

        $res = new Resource();
        $res->setResourceDisplayName('Cool resource name');

        $aakResourceRepositoryMock = $this->getMockBuilder(ResourceRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findOneBy'])
            ->getMock();
        $aakResourceRepositoryMock->expects($this->exactly(1))->method('findOneBy')->willReturn($res);

        $metric = $this->createMock(MetricsHelper::class);

        $handler = new AddBookingToCacheHandler(
            $bookingServiceMock,
            $userBookingCacheServiceMock,
            $aakResourceRepositoryMock,
            $metric,
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
            '12345'
        );

        $handler->__invoke($message);
    }
}
