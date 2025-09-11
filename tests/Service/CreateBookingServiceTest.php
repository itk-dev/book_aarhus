<?php

namespace App\Tests\Service;

use App\Entity\Resources\AAKResource;
use App\Service\CreateBookingService;
use App\Tests\AbstractBaseApiTestCase;

class CreateBookingServiceTest extends AbstractBaseApiTestCase
{
    public function testComposeBookingContentsSuccess(): void
    {
        $container = self::getContainer();
        /** @var CreateBookingService $createBookingService */
        $createBookingService = $container->get(CreateBookingService::class);

        $resource = new AAKResource();
        $resource->setResourceName('DOKK1-Lokale-Test1');
        $resource->setResourceDisplayName('DOKK1 Lokale Test1');
        $resource->setResourceMail('DOKK1-Lokale-Test1@aarhus.dk');
        $resource->setLocation('Dokk1');

        // Booking in winter time.
        $winterBooking = [
            'id' => 'booking',
            'subject' => 'Test Booking',
            'start' => '2004-02-26T15:00:00.010Z',
            'end' => '2004-02-26T15:30:00.010Z',
            'name' => 'Admin Jensen',
            'email' => 'admin_jensen@example.com',
            'resourceId' => 'DOKK1-Lokale-Test1@aarhus.dk',
            'clientBookingId' => '1234567890',
            'userId' => 'some_unqiue_user_id',
            'metaData' => [
                'data1' => 'example1',
                'data2' => 'example2',
            ],
        ];

        // Notice the plus one-hour due to winter time.
        $expected = [
            'resource' => $resource,
            'submission' => $winterBooking + [
                'from' => '26/02/2004 - 16:00 torsdag',
                'to' => '26/02/2004 - 16:30 torsdag',
            ],
            'metaData' => $winterBooking['metaData'],
            'userUniqueId' => 'UID-'.$winterBooking['userId'].'-UID',
        ];

        $actual = $createBookingService->composeBookingContents($winterBooking, $resource, $winterBooking['metaData']);

        $this->assertEquals($expected, $actual);

        // Booking in summer.
        $summerBooking = [
            'id' => 'booking',
            'subject' => 'Test Booking',
            'start' => '2004-06-26T15:00:00.010Z',
            'end' => '2004-06-26T15:30:00.010Z',
            'name' => 'Admin Jensen',
            'email' => 'admin_jensen@example.com',
            'resourceId' => 'DOKK1-Lokale-Test1@aarhus.dk',
            'clientBookingId' => '1234567890',
            'userId' => 'some_unqiue_user_id',
            'metaData' => [
                'data1' => 'example1',
                'data2' => 'example2',
            ],
        ];

        // Notice the plus two-hour due to summer time.
        $expected = [
            'resource' => $resource,
            'submission' => $summerBooking + [
                'from' => '26/06/2004 - 17:00 lørdag',
                'to' => '26/06/2004 - 17:30 lørdag',
            ],
            'metaData' => $summerBooking['metaData'],
            'userUniqueId' => 'UID-'.$summerBooking['userId'].'-UID',
        ];

        $actual = $createBookingService->composeBookingContents($summerBooking, $resource, $summerBooking['metaData']);

        $this->assertEquals($expected, $actual);
    }
}
