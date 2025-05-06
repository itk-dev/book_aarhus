<?php

namespace App\Tests\Service;

use App\Entity\Api\Booking;
use App\Entity\Main\Resource;

class NotificationServiceData
{
    public static function getBooking(): Booking
    {
        $booking = new Booking();
        $booking->setUserName('Test Testesen');
        $booking->setBody('BODY');
        $booking->setMetaData(['meta' => 'mota']);
        $booking->setResourceEmail('DOKK1-Lokale-Test1@aarhus.dk');
        $booking->setResourceName('DOKK1-Lokale-Test1');
        $booking->setSubject('Test booking');
        $booking->setUserId('1234567890');
        $booking->setUserPermission('citizen');
        $booking->setUserMail('test@example.com');
        $booking->setStartTime(new \DateTime('2042-12-13T14:00:00.0000000'));
        $booking->setEndTime(new \DateTime('2042-12-13T14:15:00.0000000'));

        return $booking;
    }

    public static function getResource(): Resource
    {
        $resource = new Resource();
        $resource->setId(1);
        $resource->setResourceMail('DOKK1-Lokale-Test1@aarhus.dk');
        $resource->setResourceName('DOKK1-Lokale-Test1');
        $resource->setResourceDisplayName('DOKK1 Lokale Test1');
        $resource->setResourceDescription('description');
        $resource->setResourceEmailText('email text');
        $resource->setLocation('Dokk1');
        $resource->setWheelchairAccessible(false);
        $resource->setVideoConferenceEquipment(true);
        $resource->setGeoCoordinates('56.15357461749666, 10.214345916610233');
        $resource->setUpdateTimestamp(new \DateTime());
        $resource->setMonitorEquipment(true);
        $resource->setCatering(false);
        $resource->setAcceptanceFlow(true);
        $resource->setCapacity(50);
        $resource->setPermissionBusinessPartner(true);
        $resource->setPermissionCitizen(true);
        $resource->setPermissionEmployee(true);
        $resource->setHasWhitelist(false);
        $resource->setDisplayName('Dokk1 test lokale');
        $resource->setCity('Aarhus');
        $resource->setStreetName('A random road');
        $resource->setPostalCode(8000);
        $resource->setResourceCategory('Lokale');
        $resource->setFormId(null);
        $resource->setResourceImage(null);

        return $resource;
    }
}
