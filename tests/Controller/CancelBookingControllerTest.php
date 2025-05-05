<?php

namespace App\Tests\Controller;

use App\Exception\UserBookingException;
use App\Service\BookingServiceInterface;
use App\Service\MetricsHelper;
use App\Service\MicrosoftGraphHelperService;
use App\Service\UserBookingCacheServiceInterface;
use App\Tests\AbstractBaseApiTestCase;
use App\Tests\Service\MicrosoftGraphBookingServiceData;
use Symfony\Bundle\SecurityBundle\Security;

class CancelBookingControllerTest extends AbstractBaseApiTestCase
{
    public function testCancelBooking(): void
    {
        $client = $this->getAuthenticatedClient();

        $metricMock = $this->createMock(MetricsHelper::class);
        $bookingServiceMock = $this->createMock(BookingServiceInterface::class);
        $userBookingCacheServiceMock = $this->createMock(UserBookingCacheServiceInterface::class);
        $microsoftGraphHelperServiceMock = $this->createMock(MicrosoftGraphHelperService::class);
        $securityMock = $this->createMock(Security::class);

        $mockICalUid1 = 'some_ical_uid';
        $mockICalUid2 = 'some_other_ical_uid';
        $mockBookingId1 = 'some_booking_id';
        $mockBookingId2 = 'some_other_booking_id';

        $bookingServiceMock
            ->expects($this->exactly(2))
            ->method('getBookingIdFromICalUid')
            ->with($this->createCallback([
                $mockICalUid1,
                $mockICalUid2,
            ]))
            ->willReturn($mockBookingId1, $mockBookingId2)
        ;

        $userBookingData = MicrosoftGraphBookingServiceData::getUserBookingData1();

        $bookingServiceMock
            ->expects($this->exactly(2))
            ->method('getBooking')
            ->with($this->createCallback([
                $mockBookingId1,
                $mockBookingId2,
            ]))
            ->willReturn($userBookingData)
        ;

        $securityMock
            ->expects($this->exactly(2))
            ->method('isGranted')
            ->willReturn(true, false)
        ;

        $bookingServiceMock
            ->expects($this->exactly(1))
            ->method('deleteBooking')
        ;

        $userBookingCacheServiceMock
            ->expects($this->exactly(1))
            ->method('deleteCacheEntryByICalUId')
            ->with($mockBookingId1)
        ;

        $container = self::getContainer();
        $container->set(MetricsHelper::class, $metricMock);
        $container->set(BookingServiceInterface::class, $bookingServiceMock);
        $container->set(MicrosoftGraphHelperService::class, $microsoftGraphHelperServiceMock);
        $container->set(UserBookingCacheServiceInterface::class, $userBookingCacheServiceMock);
        $container->set(Security::class, $securityMock);

        $data = [
            'ids' => [
                $mockICalUid1,
                $mockICalUid2,
            ]
        ];

        $response = $client->request('DELETE', '/v1/bookings/cancel', [
            'json' => $data,
        ]);

        $expected = [
            [
                'id' => $mockICalUid1,
                'status' => 'DELETED',
            ],
            [
                'id' => $mockICalUid2,
                'status' => 'FORBIDDEN',
            ],
        ];

        $this->assertEquals($expected, $response->toArray());
    }

    public function testCancelBookingNon404Exception(): void
    {
        $client = $this->getAuthenticatedClient();

        $metricMock = $this->createMock(MetricsHelper::class);
        $bookingServiceMock = $this->createMock(BookingServiceInterface::class);
        $userBookingCacheServiceMock = $this->createMock(UserBookingCacheServiceInterface::class);
        $microsoftGraphHelperServiceMock = $this->createMock(MicrosoftGraphHelperService::class);

        $mockICalUid1 = 'some_ical_uid';
        $mockBookingId1 = 'some_booking_id';

        $bookingServiceMock
            ->expects($this->exactly(1))
            ->method('getBookingIdFromICalUid')
            ->with($mockICalUid1)
            ->willReturn($mockBookingId1)
        ;

        $bookingServiceMock
            ->expects($this->exactly(1))
            ->method('getBooking')
            ->with($mockBookingId1)
            ->willThrowException(new UserBookingException('Some error'))
        ;

        $container = self::getContainer();
        $container->set(MetricsHelper::class, $metricMock);
        $container->set(BookingServiceInterface::class, $bookingServiceMock);
        $container->set(MicrosoftGraphHelperService::class, $microsoftGraphHelperServiceMock);
        $container->set(UserBookingCacheServiceInterface::class, $userBookingCacheServiceMock);

        $data = [
            'ids' => [
                $mockICalUid1,
            ]
        ];

        $response = $client->request('DELETE', '/v1/bookings/cancel', [
            'json' => $data,
        ]);

        $expected = [
            [
                'id' => $mockICalUid1,
                'status' => 'ERROR',
            ],
        ];

        $this->assertEquals($expected, $response->toArray());
    }


    public function testCancelBooking404Exception(): void
    {
        $client = $this->getAuthenticatedClient();

        $metricMock = $this->createMock(MetricsHelper::class);
        $bookingServiceMock = $this->createMock(BookingServiceInterface::class);
        $userBookingCacheServiceMock = $this->createMock(UserBookingCacheServiceInterface::class);

        $mockICalUid1 = 'some_ical_uid';

        $bookingServiceMock
            ->expects($this->exactly(1))
            ->method('getBookingIdFromICalUid')
            ->with($mockICalUid1)
            ->willReturn(NULL)
        ;

        $container = self::getContainer();
        $container->set(MetricsHelper::class, $metricMock);
        $container->set(BookingServiceInterface::class, $bookingServiceMock);
        $container->set(UserBookingCacheServiceInterface::class, $userBookingCacheServiceMock);

        $data = [
            'ids' => [
                $mockICalUid1,
            ]
        ];

        $response = $client->request('DELETE', '/v1/bookings/cancel', [
            'json' => $data,
        ]);

        $expected = [
            [
                'id' => $mockICalUid1,
                'status' => 'NOT_FOUND',
            ],
        ];

        $this->assertEquals($expected, $response->toArray());
    }

    public function testCancelBookingNonUserBookingException(): void
    {
        $client = $this->getAuthenticatedClient();

        $metricMock = $this->createMock(MetricsHelper::class);
        $bookingServiceMock = $this->createMock(BookingServiceInterface::class);
        $userBookingCacheServiceMock = $this->createMock(UserBookingCacheServiceInterface::class);
        $microsoftGraphHelperServiceMock = $this->createMock(MicrosoftGraphHelperService::class);

        $mockICalUid1 = 'some_ical_uid';
        $mockBookingId1 = 'some_booking_id';

        $bookingServiceMock
            ->expects($this->exactly(1))
            ->method('getBookingIdFromICalUid')
            ->with($mockICalUid1)
            ->willReturn($mockBookingId1)
        ;

        $bookingServiceMock
            ->expects($this->exactly(1))
            ->method('getBooking')
            ->with($mockBookingId1)
            ->willThrowException(new \Exception('Some error'))
        ;

        $container = self::getContainer();
        $container->set(MetricsHelper::class, $metricMock);
        $container->set(BookingServiceInterface::class, $bookingServiceMock);
        $container->set(MicrosoftGraphHelperService::class, $microsoftGraphHelperServiceMock);
        $container->set(UserBookingCacheServiceInterface::class, $userBookingCacheServiceMock);

        $data = [
            'ids' => [
                $mockICalUid1,
            ]
        ];

        $response = $client->request('DELETE', '/v1/bookings/cancel', [
            'json' => $data,
        ]);

        $expected = [
            [
                'id' => $mockICalUid1,
                'status' => 'ERROR',
            ],
        ];

        $this->assertEquals($expected, $response->toArray());
    }
}
