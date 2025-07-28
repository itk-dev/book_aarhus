<?php

namespace App\Tests\Controller;

use App\Entity\Resources\AAKResource;
use App\Enum\UserBookingStatusEnum;
use App\Repository\Resources\AAKResourceRepository;
use App\Service\BookingServiceInterface;
use App\Service\CreateBookingService;
use App\Service\MetricsHelper;
use App\Service\UserBookingCacheServiceInterface;
use App\Tests\AbstractBaseApiTestCase;
use Symfony\Component\HttpClient\Exception\ClientException;

class CreateBookingControllerTest extends AbstractBaseApiTestCase
{
    public function testEmptyBooking(): void
    {
        $client = $this->getAuthenticatedClient();

        $metricMock = $this->createMock(MetricsHelper::class);

        $metricMock
            ->expects($this->exactly(2))
            ->method('incMethodTotal')
            ->with(
                $this->createCallback([
                    'App\Security\ApiKeyAuthenticator::onAuthenticationSuccess',
                    'App\Controller\CreateBookingController::__invoke',
                ]),
                $this->createCallback([
                    'success',
                    MetricsHelper::INVOKE,
                ]),
            )
        ;

        $container = self::getContainer();
        $container->set(MetricsHelper::class, $metricMock);

        $data = [
            'abortIfAnyFail' => false,
            'bookings' => [
            ],
        ];

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage("An error occurred\n\nNo bookings provided.");

        $response = $client->request('POST', '/v1/bookings', [
            'json' => $data,
        ]);

        $response->getContent();
    }

    public function testInvalidDateBooking(): void
    {
        $client = $this->getAuthenticatedClient();

        $metricMock = $this->createMock(MetricsHelper::class);

        $aakResourceRepositoryMock = $this->createMock(AAKResourceRepository::class);

        $resourceIdMock = 'DOKK1-Lokale-Test1@aarhus.dk';

        $resource = new AAKResource();
        $resource->setResourceName('DOKK1-Lokale-Test1');
        $resource->setResourceDisplayName('DOKK1 Lokale Test1');
        $resource->setResourceMail('DOKK1-Lokale-Test1@aarhus.dk');
        $resource->setLocation('Dokk1');

        $aakResourceRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['resourceMail' => $resourceIdMock])
            ->willReturn($resource)
        ;

        $container = self::getContainer();
        $container->set(MetricsHelper::class, $metricMock);
        $container->set(AAKResourceRepository::class, $aakResourceRepositoryMock);

        $data = [
            'abortIfAnyFail' => true,
            'bookings' => [
                [
                    'subject' => 'Test Booking',
                    'start' => '2004-02-26T15:00:00+00:00',
                    'end' => '2004-02-26T15:30:00+00:00',
                    'name' => 'Admin Jensen',
                    'email' => 'admin_jensen@example.com',
                    'resourceId' => 'DOKK1-Lokale-Test1@aarhus.dk',
                    'clientBookingId' => '1234567890',
                    'userId' => 'some_unqiue_user_id',
                    'metaData' => [
                        'data1' => 'example1',
                        'data2' => 'example2',
                    ],
                ],
            ],
        ];

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage("An error occurred\n\nError validating booking. Aborting.");

        $response = $client->request('POST', '/v1/bookings', [
            'json' => $data,
        ]);

        $response->getContent();
    }

    public function testCreateBookingWithInvalidEmail(): void
    {
        $client = $this->getAuthenticatedClient();

        $metricMock = $this->createMock(MetricsHelper::class);

        $metricMock
            ->expects($this->exactly(3))
            ->method('incMethodTotal')
            ->with(
                $this->createCallback([
                    'App\Security\ApiKeyAuthenticator::onAuthenticationSuccess',
                    'App\Controller\CreateBookingController::__invoke',
                    'App\Controller\CreateBookingController::__invoke',
                ]),
                $this->createCallback([
                    'success',
                    MetricsHelper::INVOKE,
                    MetricsHelper::COMPLETE,
                ]),
            )
        ;

        $container = self::getContainer();
        $container->set(MetricsHelper::class, $metricMock);

        $data = [
            'abortIfAnyFail' => false,
            'bookings' => [
                [
                    'subject' => 'Test Booking',
                    'start' => '2004-02-26T15:00:00+00:00',
                    'end' => '2004-02-26T15:30:00+00:00',
                    'name' => 'Admin Jensen',
                    'email' => 'admin_jensen@example.com',
                    'resourceId' => 'invalid_email.com',
                    'clientBookingId' => '1234567890',
                    'userId' => 'some_unqiue_user_id',
                    'metaData' => [
                        'data1' => 'example1',
                        'data2' => 'example2',
                    ],
                ],
            ],
        ];

        $response = $client->request('POST', '/v1/bookings', [
            'json' => $data,
        ]);

        $expectedResponseContent = json_encode(['bookings' => []]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedResponseContent, $response->getContent());
    }

    public function testSuccessfulBooking(): void
    {
        $client = $this->getAuthenticatedClient();

        $metricMock = $this->createMock(MetricsHelper::class);
        $aakResourceRepositoryMock = $this->createMock(AAKResourceRepository::class);

        $resourceIdMock = 'DOKK1-Lokale-Test1@aarhus.dk';

        $resource = new AAKResource();
        $resource->setResourceName('DOKK1-Lokale-Test1');
        $resource->setResourceDisplayName('DOKK1 Lokale Test1');
        $resource->setResourceMail('DOKK1-Lokale-Test1@aarhus.dk');
        $resource->setLocation('Dokk1');

        $aakResourceRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['resourceMail' => $resourceIdMock])
            ->willReturn($resource)
        ;

        $mainBooking = [
            'id' => 'main_booking',
            'subject' => 'Test Booking',
            'start' => '2004-02-26T15:00:00.010Z',
            'end' => '2004-02-26T15:30:00.010Z',
            'name' => 'Admin Jensen',
            'email' => 'admin_jensen@example.com',
            'resourceId' => $resourceIdMock,
            'clientBookingId' => '1234567890',
            'userId' => 'some_unqiue_user_id',
            'metaData' => [
                'data1' => 'example1',
                'data2' => 'example2',
            ],
        ];

        $bookingServiceMock = $this->createMock(BookingServiceInterface::class);

        $createBookingServiceMock = $this->createMock(CreateBookingService::class);

        $bodyMock = [
            'resource' => $resource,
            'submission' => $mainBooking + [
                'from' => '26/02/2004 - 15:00 torsdag',
                'to' => '26/02/2004 - 15:30 torsdag',
            ],
            'metaData' => $mainBooking['metaData'],
            'userUniqueId' => 'UID-'.$mainBooking['userId'].'-UID',
        ];

        $createBookingServiceMock
            ->expects($this->once())
            ->method('composeBookingContents')
            ->with($mainBooking, $resource, $mainBooking['metaData'])
            ->willReturn($bodyMock)
        ;

        $createBookingServiceMock
            ->expects($this->once())
            ->method('renderContentsAsHtml')
            ->with($bodyMock)
            ->willReturn('test')
        ;

        $createdBookingMock = [
            'id' => 'booking_id_12345',
            'iCalUId' => 'iCalUId_12345',
            'status' => UserBookingStatusEnum::ACCEPTED->name,
        ];

        $createBookingServiceMock
            ->expects($this->once())
            ->method('createBooking')
            ->willReturn($createdBookingMock)
        ;

        $userBookingCacheServiceMock = $this->createMock(UserBookingCacheServiceInterface::class);

        $container = self::getContainer();
        $container->set(MetricsHelper::class, $metricMock);
        $container->set(AAKResourceRepository::class, $aakResourceRepositoryMock);
        $container->set(CreateBookingService::class, $createBookingServiceMock);
        $container->set(BookingServiceInterface::class, $bookingServiceMock);
        $container->set(UserBookingCacheServiceInterface::class, $userBookingCacheServiceMock);

        $data = [
            'abortIfAnyFail' => false,
            'bookings' => [
                $mainBooking,
            ],
        ];

        $response = $client->request('POST', '/v1/bookings', [
            'json' => $data,
        ]);

        $content = $response->toArray();

        $expected = [
            'bookings' => [
                [
                    'input' => $mainBooking,
                    'status' => 'SUCCESS',
                    'createdBooking' => $createdBookingMock,
                ],
            ],
        ];

        $this->assertEquals($expected, $content);
    }

    public function testOneSuccessfulAndOneFailedBookingWithoutAbortIfAnyFail(): void
    {
        $client = $this->getAuthenticatedClient();

        $metricMock = $this->createMock(MetricsHelper::class);
        $aakResourceRepositoryMock = $this->createMock(AAKResourceRepository::class);

        $resourceIdMock = 'DOKK1-Lokale-Test1@aarhus.dk';

        $resource = new AAKResource();
        $resource->setResourceName('DOKK1-Lokale-Test1');
        $resource->setResourceDisplayName('DOKK1 Lokale Test1');
        $resource->setResourceMail('DOKK1-Lokale-Test1@aarhus.dk');
        $resource->setLocation('Dokk1');

        $aakResourceRepositoryMock
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with(['resourceMail' => $resourceIdMock])
            ->willReturn($resource)
        ;

        $mainBooking = [
            'id' => 'main_booking',
            'subject' => 'Test Booking',
            'start' => '2004-02-26T15:00:00.010Z',
            'end' => '2004-02-26T15:30:00.010Z',
            'name' => 'Admin Jensen',
            'email' => 'admin_jensen@example.com',
            'resourceId' => $resourceIdMock,
            'clientBookingId' => '1234567890',
            'userId' => 'some_unqiue_user_id',
            'metaData' => [
                'data1' => 'example1',
                'data2' => 'example2',
            ],
        ];

        $bufferBooking = [
            'id' => 'buffer_after_booking',
            'subject' => 'Test Booking',
            'start' => '2004-02-26T15:30:00.010Z',
            'end' => '2004-02-26T16:00:00.010Z',
            'name' => 'Admin Jensen',
            'email' => 'admin_jensen@example.com',
            'resourceId' => $resourceIdMock,
            'clientBookingId' => '1234567890',
            'userId' => 'some_unqiue_user_id',
            'metaData' => [
                'data1' => 'example1',
                'data2' => 'example2',
            ],
        ];

        $bookingServiceMock = $this->createMock(BookingServiceInterface::class);

        $createBookingServiceMock = $this->createMock(CreateBookingService::class);

        $mainBodyMock = [
            'resource' => $resource,
            'submission' => $mainBooking + [
                'from' => '26/02/2004 - 15:00 torsdag',
                'to' => '26/02/2004 - 15:30 torsdag',
            ],
            'metaData' => $mainBooking['metaData'],
            'userUniqueId' => 'UID-'.$mainBooking['userId'].'-UID',
        ];

        $bufferBodyMock = [
            'resource' => $resource,
            'submission' => $bufferBooking + [
                'from' => '26/02/2004 - 15:30 torsdag',
                'to' => '26/02/2004 - 16:00 torsdag',
            ],
            'metaData' => $bufferBooking['metaData'],
            'userUniqueId' => 'UID-'.$bufferBooking['userId'].'-UID',
        ];

        $createBookingServiceMock
            ->expects($this->exactly(2))
            ->method('composeBookingContents')
            ->with(
                $this->createCallback([
                    $mainBooking,
                    $bufferBooking,
                ]),
                $this->createCallback([
                    $resource,
                    $resource,
                ]),
                $this->createCallback([
                    $mainBooking['metaData'],
                    $bufferBooking['metaData'],
                ]),
            )
            ->willReturn($mainBodyMock, $bufferBodyMock)
        ;

        $createBookingServiceMock
            ->expects($this->exactly(2))
            ->method('renderContentsAsHtml')
            ->with($this->createCallback([
                $mainBodyMock,
                $bufferBodyMock,
            ]))
            ->willReturn('test_html')
        ;

        $createdMainBookingMock = [
            'id' => 'main_booking',
            'iCalUId' => 'iCalUId_12345',
            'status' => UserBookingStatusEnum::ACCEPTED->name,
        ];

        $createdBufferBookingMock = [
            'id' => 'bufferAfter_booking',
            'iCalUId' => 'iCalUId_54321',
            'status' => UserBookingStatusEnum::DECLINED->name,
        ];

        $createBookingServiceMock
            ->expects($this->exactly(2))
            ->method('createBooking')
            ->willReturn($createdMainBookingMock, $createdBufferBookingMock)
        ;

        $userBookingCacheServiceMock = $this->createMock(UserBookingCacheServiceInterface::class);

        $container = self::getContainer();
        $container->set(MetricsHelper::class, $metricMock);
        $container->set(AAKResourceRepository::class, $aakResourceRepositoryMock);
        $container->set(CreateBookingService::class, $createBookingServiceMock);
        $container->set(BookingServiceInterface::class, $bookingServiceMock);
        $container->set(UserBookingCacheServiceInterface::class, $userBookingCacheServiceMock);

        $data = [
            'abortIfAnyFail' => false,
            'bookings' => [
                $mainBooking,
                $bufferBooking,
            ],
        ];

        $response = $client->request('POST', '/v1/bookings', [
            'json' => $data,
        ]);

        $content = $response->toArray();

        $expected = [
            'bookings' => [
                [
                    'input' => $mainBooking,
                    'status' => 'SUCCESS',
                    'createdBooking' => $createdMainBookingMock,
                ],
                [
                    'input' => $bufferBooking,
                    'status' => 'ERROR',
                    'createdBooking' => null,
                ],
            ],
        ];

        $this->assertEquals($expected, $content);
    }

    public function testOneSuccessfulAndOneFailedBookingWithAbortIfAnyFail(): void
    {
        $client = $this->getAuthenticatedClient();

        $metricMock = $this->createMock(MetricsHelper::class);
        $aakResourceRepositoryMock = $this->createMock(AAKResourceRepository::class);

        $resourceIdMock = 'DOKK1-Lokale-Test1@aarhus.dk';

        $resource = new AAKResource();
        $resource->setResourceName('DOKK1-Lokale-Test1');
        $resource->setResourceDisplayName('DOKK1 Lokale Test1');
        $resource->setResourceMail('DOKK1-Lokale-Test1@aarhus.dk');
        $resource->setLocation('Dokk1');

        $aakResourceRepositoryMock
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with(['resourceMail' => $resourceIdMock])
            ->willReturn($resource)
        ;

        $mainBooking = [
            'id' => 'main_booking',
            'subject' => 'Test Booking',
            'start' => '2004-02-26T15:00:00.010Z',
            'end' => '2004-02-26T15:30:00.010Z',
            'name' => 'Admin Jensen',
            'email' => 'admin_jensen@example.com',
            'resourceId' => $resourceIdMock,
            'clientBookingId' => '1234567890',
            'userId' => 'some_unqiue_user_id',
            'metaData' => [
                'data1' => 'example1',
                'data2' => 'example2',
            ],
        ];

        $bufferBooking = [
            'id' => 'buffer_after_booking',
            'subject' => 'Test Booking',
            'start' => '2004-02-26T15:30:00.010Z',
            'end' => '2004-02-26T16:00:00.010Z',
            'name' => 'Admin Jensen',
            'email' => 'admin_jensen@example.com',
            'resourceId' => $resourceIdMock,
            'clientBookingId' => '1234567890',
            'userId' => 'some_unqiue_user_id',
            'metaData' => [
                'data1' => 'example1',
                'data2' => 'example2',
            ],
        ];

        $bookingServiceMock = $this->createMock(BookingServiceInterface::class);

        $createBookingServiceMock = $this->createMock(CreateBookingService::class);

        $mainBodyMock = [
            'resource' => $resource,
            'submission' => $mainBooking + [
                'from' => '26/02/2004 - 15:00 torsdag',
                'to' => '26/02/2004 - 15:30 torsdag',
            ],
            'metaData' => $mainBooking['metaData'],
            'userUniqueId' => 'UID-'.$mainBooking['userId'].'-UID',
        ];

        $bufferBodyMock = [
            'resource' => $resource,
            'submission' => $bufferBooking + [
                'from' => '26/02/2004 - 15:30 torsdag',
                'to' => '26/02/2004 - 16:00 torsdag',
            ],
            'metaData' => $bufferBooking['metaData'],
            'userUniqueId' => 'UID-'.$bufferBooking['userId'].'-UID',
        ];

        $createBookingServiceMock
            ->expects($this->exactly(2))
            ->method('composeBookingContents')
            ->with(
                $this->createCallback([
                    $mainBooking,
                    $bufferBooking,
                ]),
                $this->createCallback([
                    $resource,
                    $resource,
                ]),
                $this->createCallback([
                    $mainBooking['metaData'],
                    $bufferBooking['metaData'],
                ]),
            )
            ->willReturn($mainBodyMock, $bufferBodyMock)
        ;

        $createBookingServiceMock
            ->expects($this->exactly(2))
            ->method('renderContentsAsHtml')
            ->with($this->createCallback([
                $mainBodyMock,
                $bufferBodyMock,
            ]))
            ->willReturn('test_html')
        ;

        $createdMainBookingMock = [
            'id' => 'main_booking',
            'iCalUId' => 'iCalUId_12345',
            'status' => UserBookingStatusEnum::ACCEPTED->name,
        ];

        $createdBufferBookingMock = [
            'id' => 'bufferAfter_booking',
            'iCalUId' => 'iCalUId_54321',
            'status' => UserBookingStatusEnum::DECLINED->name,
        ];

        $createBookingServiceMock
            ->expects($this->exactly(2))
            ->method('createBooking')
            ->willReturn($createdMainBookingMock, $createdBufferBookingMock)
        ;

        $userBookingCacheServiceMock = $this->createMock(UserBookingCacheServiceInterface::class);

        $container = self::getContainer();
        $container->set(MetricsHelper::class, $metricMock);
        $container->set(AAKResourceRepository::class, $aakResourceRepositoryMock);
        $container->set(CreateBookingService::class, $createBookingServiceMock);
        $container->set(BookingServiceInterface::class, $bookingServiceMock);
        $container->set(UserBookingCacheServiceInterface::class, $userBookingCacheServiceMock);

        $data = [
            'abortIfAnyFail' => true,
            'bookings' => [
                $mainBooking,
                $bufferBooking,
            ],
        ];

        $response = $client->request('POST', '/v1/bookings', [
            'json' => $data,
        ]);

        $content = $response->toArray();

        $expected = [
            'bookings' => [
                [
                    'input' => $mainBooking,
                    'status' => 'CANCELLED',
                    'createdBooking' => $createdMainBookingMock,
                ],
                [
                    'input' => $bufferBooking,
                    'status' => 'ERROR',
                    'createdBooking' => null,
                ],
            ],
        ];

        $this->assertEquals($expected, $content);
    }

    public function testIntervalConflictWithoutAbortIfAnyFail(): void
    {
        $client = $this->getAuthenticatedClient();

        $metricMock = $this->createMock(MetricsHelper::class);
        $aakResourceRepositoryMock = $this->createMock(AAKResourceRepository::class);

        $resourceIdMock = 'DOKK1-Lokale-Test1@aarhus.dk';

        $resource = new AAKResource();
        $resource->setResourceName('DOKK1-Lokale-Test1');
        $resource->setResourceDisplayName('DOKK1 Lokale Test1');
        $resource->setResourceMail('DOKK1-Lokale-Test1@aarhus.dk');
        $resource->setLocation('Dokk1');
        $resource->setAcceptConflict(false);

        $aakResourceRepositoryMock
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with(['resourceMail' => $resourceIdMock])
            ->willReturn($resource)
        ;

        $mainBooking = [
            'id' => 'main_booking',
            'subject' => 'Test Booking',
            'start' => '2004-02-26T15:00:00.010Z',
            'end' => '2004-02-26T15:30:00.010Z',
            'name' => 'Admin Jensen',
            'email' => 'admin_jensen@example.com',
            'resourceId' => $resourceIdMock,
            'clientBookingId' => '1234567890',
            'userId' => 'some_unqiue_user_id',
            'metaData' => [
                'data1' => 'example1',
                'data2' => 'example2',
            ],
        ];

        $bufferBooking = [
            'id' => 'buffer_after_booking',
            'subject' => 'Test Booking',
            'start' => '2004-02-26T15:30:00.010Z',
            'end' => '2004-02-26T16:00:00.010Z',
            'name' => 'Admin Jensen',
            'email' => 'admin_jensen@example.com',
            'resourceId' => $resourceIdMock,
            'clientBookingId' => '1234567890',
            'userId' => 'some_unqiue_user_id',
            'metaData' => [
                'data1' => 'example1',
                'data2' => 'example2',
            ],
        ];

        $bookingServiceMock = $this->createMock(BookingServiceInterface::class);

        $createBookingServiceMock = $this->createMock(CreateBookingService::class);

        $mainBodyMock = [
            'resource' => $resource,
            'submission' => $mainBooking + [
                'from' => '26/02/2004 - 15:00 torsdag',
                'to' => '26/02/2004 - 15:30 torsdag',
            ],
            'metaData' => $mainBooking['metaData'],
            'userUniqueId' => 'UID-'.$mainBooking['userId'].'-UID',
        ];

        $bufferBodyMock = [
            'resource' => $resource,
            'submission' => $bufferBooking + [
                'from' => '26/02/2004 - 15:30 torsdag',
                'to' => '26/02/2004 - 16:00 torsdag',
            ],
            'metaData' => $bufferBooking['metaData'],
            'userUniqueId' => 'UID-'.$bufferBooking['userId'].'-UID',
        ];

        $createBookingServiceMock
            ->expects($this->exactly(2))
            ->method('composeBookingContents')
            ->with(
                $this->createCallback([
                    $mainBooking,
                    $bufferBooking,
                ]),
                $this->createCallback([
                    $resource,
                    $resource,
                ]),
                $this->createCallback([
                    $mainBooking['metaData'],
                    $bufferBooking['metaData'],
                ]),
            )
            ->willReturn($mainBodyMock, $bufferBodyMock)
        ;

        $createBookingServiceMock
            ->expects($this->exactly(2))
            ->method('renderContentsAsHtml')
            ->with($this->createCallback([
                $mainBodyMock,
                $bufferBodyMock,
            ]))
            ->willReturn('test_html')
        ;

        $conflicts = [
            'DOKK1-Lokale-Test1@aarhus.dk' => [
                [
                    'startTime' => '2004-02-26T15:45:00.010Z',
                    'endTime' => '2004-02-26T16:30:00.010Z',
                ],
            ],
        ];

        $bookingServiceMock
            ->expects($this->exactly(2))
            ->method('getBusyIntervals')
            ->willReturn($conflicts)
        ;

        $userBookingCacheServiceMock = $this->createMock(UserBookingCacheServiceInterface::class);

        $container = self::getContainer();
        $container->set(MetricsHelper::class, $metricMock);
        $container->set(AAKResourceRepository::class, $aakResourceRepositoryMock);
        $container->set(CreateBookingService::class, $createBookingServiceMock);
        $container->set(BookingServiceInterface::class, $bookingServiceMock);
        $container->set(UserBookingCacheServiceInterface::class, $userBookingCacheServiceMock);

        $data = [
            'abortIfAnyFail' => false,
            'bookings' => [
                $mainBooking,
                $bufferBooking,
            ],
        ];

        $response = $client->request('POST', '/v1/bookings', [
            'json' => $data,
        ]);

        $content = $response->toArray();

        $expected = [
            'bookings' => [
                [
                    'input' => $mainBooking,
                    'status' => 'CONFLICT',
                    'createdBooking' => null,
                ],
                [
                    'input' => $bufferBooking,
                    'status' => 'CONFLICT',
                    'createdBooking' => null,
                ],
            ],
        ];

        $this->assertEquals($expected, $content);
    }
}
