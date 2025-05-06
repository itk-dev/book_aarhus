<?php

namespace App\Tests\Controller;

use App\Controller\GetStatusByIdsController;
use App\Entity\Api\UserBooking;
use App\Enum\UserBookingStatusEnum;
use App\Interface\BookingServiceInterface;
use App\Interface\UserBookingCacheServiceInterface;
use App\Service\MetricsHelper;
use App\Tests\AbstractBaseApiTestCase;
use App\Tests\Service\MicrosoftGraphBookingServiceData;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class GetStatusByIdControllerTest extends AbstractBaseApiTestCase
{
    use InteractsWithMessenger;

    /**
     * @throws \Exception
     */
    public function testGetStatusByIdController(): void
    {
        $booking = MicrosoftGraphBookingServiceData::getUserBookingData1();

        $bookingServiceMock = $this->getMockBuilder(BookingServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $bookingServiceMock->expects($this->exactly(1))->method('getBooking')->willReturn($booking);
        $userBooking = new UserBooking();
        $userBooking->status = UserBookingStatusEnum::ACCEPTED->name;
        $bookingServiceMock->expects($this->exactly(1))->method('getUserBookingFromApiData')->willReturn($userBooking);

        $userBookingCacheServiceMock = $this->getMockBuilder(UserBookingCacheServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userBookingCacheServiceMock->expects($this->exactly(1))->method('changeCacheEntry');

        $metric = $this->createMock(MetricsHelper::class);

        $controller = new GetStatusByIdsController($bookingServiceMock, $userBookingCacheServiceMock, $metric);

        $response = $controller->__invoke(new Request([], [], [], [], [], [], json_encode([
            'ids' => ['1234567890'],
        ])));

        $resp = json_decode($response->getContent(), true);

        $this->assertEquals($resp[0], ['exchangeId' => '1234567890', 'status' => 'ACCEPTED']);
    }
}
