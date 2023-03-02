<?php

namespace App\Tests\Service;

use App\Exception\BookingCreateConflictException;
use App\Exception\MicrosoftGraphCommunicationException;
use App\Exception\UserBookingException;
use App\Service\MicrosoftGraphBookingService;
use App\Service\MicrosoftGraphHelperService;
use App\Tests\AbstractBaseApiTestCase;
use Microsoft\Graph\Http\GraphRequest;
use Microsoft\Graph\Http\GraphResponse;

class MicrosoftGraphBookingServiceTest extends AbstractBaseApiTestCase
{
    public function testGetBookingData(): void
    {
        $microsoftGraphHelperServiceMock = $this->getMockBuilder(MicrosoftGraphHelperService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request', 'authenticateAsServiceAccount'])
            ->getMock();

        $microsoftGraphHelperServiceMock->method('authenticateAsServiceAccount')->willReturn('1234');

        $microsoftGraphHelperServiceMock->method('request')->willReturn(
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([
                    'value' => [
                        [
                            'data' => 'test',
                        ],
                    ],
                ]),
            )
        );

        $graphService = new MicrosoftGraphBookingService('test@example.com', 'test', $microsoftGraphHelperServiceMock);

        $bookingData = $graphService->getBooking('1234');

        $this->assertEquals([
            'value' => [
                [
                    'data' => 'test',
                ],
            ],
        ], $bookingData);
    }

    public function testCreateBodyUserId(): void
    {
        $microsoftGraphHelperServiceMock = $this->getMockBuilder(MicrosoftGraphHelperService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $graphService = new MicrosoftGraphBookingService('test@example.com', 'test', $microsoftGraphHelperServiceMock);

        $userId = $graphService->createBodyUserId('useridtest');

        $this->assertEquals('UID-useridtest-UID', $userId);
    }

    public function testGetUserBookingFromApiData(): void
    {
        $microsoftGraphHelperServiceMock = $this->getMockBuilder(MicrosoftGraphHelperService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $graphService = new MicrosoftGraphBookingService('test@example.com', 'test', $microsoftGraphHelperServiceMock);

        $data = MicrosoftGraphBookingServiceData::getUserBookingData1();

        $userBooking = $graphService->getUserBookingFromApiData($data);

        $this->assertEquals('ICALUID12345678', $userBooking->iCalUId);
        $this->assertEquals('DOKK1-Lokale-Test1@aarhus.dk', $userBooking->resourceMail);
        $this->assertEquals('DOKK1-Lokale-Test1', $userBooking->resourceName);
        $this->assertEquals('Test Booking', $userBooking->subject);
        $this->assertEquals('ACCEPTED', $userBooking->status);
        $this->assertEquals(false, $userBooking->ownedByServiceAccount);
        $this->assertEquals(true, $userBooking->expired);
        $this->assertEquals('ID123456', $userBooking->id);
        $this->assertEquals('INSTANT', $userBooking->bookingType);
        $this->assertEquals('DOKK1-Lokale-Test1', $userBooking->displayName);
        $this->assertEquals((new \DateTime('2022-12-13T14:00:00.0000000Z'))->format('c'), $userBooking->start->format('c'));
        $this->assertEquals((new \DateTime('2022-12-13T14:15:00.0000000Z'))->format('c'), $userBooking->end->format('c'));

        $data['responseStatus'] = [
            'response' => 'declined',
            'time' => '2022-12-13T11:58:15.4328965Z',
        ];

        $userBooking = $graphService->getUserBookingFromApiData($data);

        $this->assertEquals('DECLINED', $userBooking->status);

        $data['responseStatus'] = [
            'response' => 'none',
            'time' => '2022-12-13T11:58:15.4328965Z',
        ];
        $data['organizer'] = ['emailAddress' => ['name' => 'test', 'address' => 'test@example.com']];

        $userBooking = $graphService->getUserBookingFromApiData($data);

        $this->assertEquals('AWAITING_APPROVAL', $userBooking->status);
        $this->assertEquals(true, $userBooking->ownedByServiceAccount);

        try {
            $data['attendees'] = [];

            $graphService->getUserBookingFromApiData($data);
        } catch (UserBookingException $e) {
            $this->assertEquals(400, $e->getCode());
        }
    }

    public function testGetUserBookings(): void
    {
        $microsoftGraphHelperServiceMock = $this->getMockBuilder(MicrosoftGraphHelperService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request', 'authenticateAsServiceAccount'])
            ->getMock();

        $microsoftGraphHelperServiceMock->method('authenticateAsServiceAccount')->willReturn('1234');

        $resp1 = new GraphResponse(
            new GraphRequest('POST', '/', '123', 'http://localhost', 'v1'),
            json_encode(MicrosoftGraphBookingServiceData::getUserBookings1()),
        );

        $resp2 = new GraphResponse(
            new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
            json_encode(MicrosoftGraphBookingServiceData::getUserBookingData2()),
        );

        $resp3 = new GraphResponse(
            new GraphRequest('POST', '/', '123', 'http://localhost', 'v1'),
            json_encode(MicrosoftGraphBookingServiceData::getUserBookings2()),
        );

        $resp4 = new GraphResponse(
            new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
            json_encode(MicrosoftGraphBookingServiceData::getUserBookingData1()),
        );

        $microsoftGraphHelperServiceMock->method('request')->willReturn(
            $resp1,
            $resp2,
            $resp2,
            $resp2,
            $resp2,
            $resp2,
            $resp3,
            $resp2,
            $resp4,
        );

        $graphService = new MicrosoftGraphBookingService('test@example.com', 'test', $microsoftGraphHelperServiceMock);

        $userBookings = $graphService->getUserBookings('1234567890');

        $this->assertCount(7, $userBookings);
    }

    /**
     * @throws MicrosoftGraphCommunicationException
     * @throws UserBookingException
     */
    public function testDeleteBooking(): void
    {
        $microsoftGraphHelperServiceMock = $this->getMockBuilder(MicrosoftGraphHelperService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request', 'authenticateAsServiceAccount'])
            ->getMock();

        $microsoftGraphHelperServiceMock->method('authenticateAsServiceAccount')->willReturn('1234');

        $microsoftGraphHelperServiceMock->method('request')->willReturn(
            // 2. Test ok 204
            // deleteBookingFromServiceAccount: request
            new GraphResponse(
                new GraphRequest('POST', '/', '123', 'http://localhost', 'v1'),
                json_encode(MicrosoftGraphBookingServiceData::getUserBookingData2()),
                204,
            ),
            // deleteBookingFromResource: getEventFromResourceByICalUid
            new GraphResponse(
                new GraphRequest('POST', '/', '123', 'http://localhost', 'v1'),
                json_encode([
                    'value' => [
                        MicrosoftGraphBookingServiceData::getUserBookingData2(),
                    ],
                ]),
                200,
            ),
            // deleteBookingFromResource: request
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([]),
                204,
            ),
            // 3. Test when request reports error.
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([]),
                400,
            ),
            // 4. Owned by service account
            // deleteBookingFromResource: getEventFromResourceByICalUid
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([]),
                200,
            ),
            // 5. Owned by service account, not request 204
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([
                    'value' => [
                        MicrosoftGraphBookingServiceData::getUserBookingData2(),
                    ],
                ]),
                200,
            ),
            new GraphResponse(
                new GraphRequest('POST', '/', '123', 'http://localhost', 'v1'),
                json_encode([]),
                500,
            ),
            // 6. Owned by service account, not request 204
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([
                    'value' => [
                        MicrosoftGraphBookingServiceData::getUserBookingData2(),
                    ],
                ]),
                200,
            ),
            new GraphResponse(
                new GraphRequest('POST', '/', '123', 'http://localhost', 'v1'),
                json_encode([]),
                204,
            ),
            new GraphResponse(
                new GraphRequest('POST', '/', '123', 'http://localhost', 'v1'),
                json_encode([]),
                204,
            ),
        );

        $graphService = new MicrosoftGraphBookingService('test@example.com', 'test', $microsoftGraphHelperServiceMock);

        // 1. Expired booking.
        $userBooking = $graphService->getUserBookingFromApiData(MicrosoftGraphBookingServiceData::getUserBookingData1());

        try {
            $graphService->deleteBooking($userBooking);
        } catch (UserBookingException $e) {
            $this->assertEquals('Booking is expired. Cannot be deleted.', $e->getMessage());
        }

        // 2. Test ok 204
        $userBooking = $graphService->getUserBookingFromApiData(MicrosoftGraphBookingServiceData::getUserBookingData2());

        // Should not create an exception.
        $graphService->deleteBooking($userBooking);

        // 3. Test when request reports error.
        try {
            $graphService->deleteBooking($userBooking);
        } catch (UserBookingException $e) {
            $this->assertEquals(400, $e->getCode());
        }

        // 4. Owned by service account.
        $userBooking = $graphService->getUserBookingFromApiData(MicrosoftGraphBookingServiceData::getUserBookingData2());
        $userBooking->ownedByServiceAccount = true;

        try {
            $graphService->deleteBooking($userBooking);
        } catch (UserBookingException $e) {
            $this->assertEquals(404, $e->getCode());
        }

        // 5. Owned by service account, not request 204
        try {
            $graphService->deleteBooking($userBooking);
        } catch (UserBookingException $e) {
            $this->assertEquals(500, $e->getCode());
        }

        // 6. Owned by service account, not request 204, removed from resource
        try {
            $graphService->deleteBooking($userBooking);
        } catch (UserBookingException $e) {
            $this->assertEquals(500, $e->getCode());
        }
    }

    public function testUpdateBooking(): void
    {
        $microsoftGraphHelperServiceMock = $this->getMockBuilder(MicrosoftGraphHelperService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request', 'authenticateAsServiceAccount'])
            ->getMock();

        $microsoftGraphHelperServiceMock->method('authenticateAsServiceAccount')->willReturn('1234');

        $microsoftGraphHelperServiceMock->method('request')->willReturn(
            // 2. No conflict: isBookingConflict
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([
                    'value' => [
                        MicrosoftGraphBookingServiceData::getUserBookingData2(),
                    ],
                ]),
                200,
            ),
            // getEventFromResourceByICalUid
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([
                    'value' => [
                        MicrosoftGraphBookingServiceData::getUserBookingData2(),
                    ],
                ]),
                200,
            ),
            // request
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([]),
                200,
            ),
            // 3. No conflict, owned by service account. isBookingConflict
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([
                    'value' => [
                        MicrosoftGraphBookingServiceData::getUserBookingData2(),
                    ],
                ]),
                200,
            ),
            // request
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([]),
                200,
            ),
            // 3. Conflict, owned by service account. isBookingConflict
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([
                    'value' => [
                        MicrosoftGraphBookingServiceData::getUserBookingData3(),
                        MicrosoftGraphBookingServiceData::getUserBookingData2(),
                    ],
                ]),
                200,
            ),
        );

        $graphService = new MicrosoftGraphBookingService('test@example.com', 'test', $microsoftGraphHelperServiceMock);

        // 1. Expired
        $userBooking = $graphService->getUserBookingFromApiData(MicrosoftGraphBookingServiceData::getUserBookingData1());

        try {
            $graphService->updateBooking($userBooking);
        } catch (UserBookingException $e) {
            $this->assertEquals(400, $e->getCode());
        }

        // 2. No conflict
        $userBooking = $graphService->getUserBookingFromApiData(MicrosoftGraphBookingServiceData::getUserBookingData2());

        $result = $graphService->updateBooking($userBooking);

        $this->assertEquals(200, $result);

        // 3. No conflict, owned by service account.
        $userBooking = $graphService->getUserBookingFromApiData(MicrosoftGraphBookingServiceData::getUserBookingData2());
        $userBooking->ownedByServiceAccount = true;

        $result = $graphService->updateBooking($userBooking);

        $this->assertEquals(200, $result);

        // 4. Conflict.
        $userBooking = $graphService->getUserBookingFromApiData(MicrosoftGraphBookingServiceData::getUserBookingData2());

        try {
            $graphService->updateBooking($userBooking);
        } catch (UserBookingException $e) {
            $this->assertEquals(409, $e->getCode());
        }
    }

    public function testCreateBookingForResource(): void
    {
        $microsoftGraphHelperServiceMock = $this->getMockBuilder(MicrosoftGraphHelperService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request', 'authenticateAsServiceAccount'])
            ->getMock();

        $microsoftGraphHelperServiceMock->method('authenticateAsServiceAccount')->willReturn('1234');

        $microsoftGraphHelperServiceMock->method('request')->willReturn(
            // 1. No conflict
            // Empty interval.
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([
                    'value' => [],
                ]),
                200,
            ),
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode(MicrosoftGraphBookingServiceData::getUserBookingData2()),
                201,
            ),
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([
                    'value' => [
                        MicrosoftGraphBookingServiceData::getUserBookingData2(),
                    ],
                ]),
                200,
            ),
            // 2. Conflict 1
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([
                    'value' => [
                        MicrosoftGraphBookingServiceData::getUserBookingData2(),
                    ],
                ]),
                200,
            ),
            // 3. Conflict 2
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([
                    'value' => [],
                ]),
                200,
            ),
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode(MicrosoftGraphBookingServiceData::getUserBookingData2()),
                201,
            ),
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([
                    'value' => [
                        MicrosoftGraphBookingServiceData::getUserBookingData2(),
                        MicrosoftGraphBookingServiceData::getUserBookingData3(),
                    ],
                ]),
                200,
            ),
            // Delete
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([]),
                204,
            ),
        );

        $graphService = new MicrosoftGraphBookingService('test@example.com', 'test', $microsoftGraphHelperServiceMock);

        // 1. No conflict
        $data = [
            'name' => 'DOKK1-Lokale-Test1',
            'address' => 'DOKK1-Lokale-Test1@aarhus.dk',
            'subject' => 'test',
            'body' => '',
            'start' => new \DateTime('2042-12-13T14:00:00.0000000'),
            'end' => new \DateTime('2042-12-13T14:15:00.0000000'),
        ];

        $content = $graphService->createBookingForResource($data['address'], $data['name'], $data['subject'], $data['body'], $data['start'], $data['end']);

        $this->assertEquals(MicrosoftGraphBookingServiceData::getUserBookingData2(), $content);

        // 2. Conflict 1
        try {
            $graphService->createBookingForResource($data['address'], $data['name'], $data['subject'], $data['body'], $data['start'], $data['end']);
        } catch (BookingCreateConflictException $e) {
            $this->assertEquals(409, $e->getCode());
        }

        // 3. Conflict 2
        try {
            $graphService->createBookingForResource($data['address'], $data['name'], $data['subject'], $data['body'], $data['start'], $data['end']);
        } catch (BookingCreateConflictException $e) {
            $this->assertEquals(409, $e->getCode());
        }
    }

    public function testCreateBookingInviteResource(): void
    {
        $microsoftGraphHelperServiceMock = $this->getMockBuilder(MicrosoftGraphHelperService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request', 'authenticateAsServiceAccount'])
            ->getMock();

        $microsoftGraphHelperServiceMock->method('authenticateAsServiceAccount')->willReturn('1234');

        $microsoftGraphHelperServiceMock->method('request')->willReturn(
            // 1. Success
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode(MicrosoftGraphBookingServiceData::getUserBookingData2()),
                201,
            ),
            // 2. Error
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([]),
                400,
            ),
        );

        $graphService = new MicrosoftGraphBookingService('test@example.com', 'test', $microsoftGraphHelperServiceMock);

        $data = [
            'name' => 'DOKK1-Lokale-Test1',
            'address' => 'DOKK1-Lokale-Test1@aarhus.dk',
            'subject' => 'test',
            'body' => '',
            'start' => new \DateTime('2042-12-13T14:00:00.0000000'),
            'end' => new \DateTime('2042-12-13T14:15:00.0000000'),
        ];

        // 1. Success
        $content = $graphService->createBookingInviteResource($data['address'], $data['name'], $data['subject'], $data['body'], $data['start'], $data['end']);

        $this->assertEquals(MicrosoftGraphBookingServiceData::getUserBookingData2(), $content);

        // 2. Error
        try {
            $graphService->createBookingInviteResource($data['address'], $data['name'], $data['subject'], $data['body'], $data['start'], $data['end']);
        } catch (MicrosoftGraphCommunicationException $e) {
            $this->assertEquals(400, $e->getCode());
        }
    }
}
