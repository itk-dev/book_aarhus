<?php

namespace App\DataFixtures;

use App\Entity\Main\Resource;
use App\Entity\Main\CvrWhitelist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ResourceFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $resources = [
            [
                'resourceEmail' => 'DOKK1-Lokale-Test1@aarhus.dk',
                'resourceName' => 'DOKK1-Lokale-Test1',
                'resourceDescription' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'resourceEmailText' => '',
                'location' => 'LOCATION1',
                'wheelChairAccessible' => true,
                'videoConferenceEquipment' => false,
                'monitorEquipment' => false,
                'catering' => false,
                'acceptanceFlow' => false,
                'capacity' => 10,
                'permissionBusinessPartner' => true,
                'permissionCitizen' => true,
                'permissionEmployee' => true,
                'displayName' => 'Dokk1 Lokale Test 1',
                'city' => 'Aarhus',
                'streetName' => 'Hack Kampmanns Pl. 2',
                'postalCode' => 8000,
                'resourceCategory' => 'Lokale',
                'formId' => null,
                'resourceImage' => 'https://placekitten.com/g/200/200',
                'resourceDisplayName' => 'Dokk1 Lokale Test 1',
                'locationDisplayName' => 'Location 1',
                'acceptConflict' => true,
            ],
            [
                'resourceEmail' => 'dokk1-lokale-test2@aarhus.dk',
                'resourceName' => 'dokk1-lokale-test2',
                'resourceDescription' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'resourceEmailText' => '',
                'location' => 'LOCATION1',
                'wheelChairAccessible' => false,
                'videoConferenceEquipment' => true,
                'monitorEquipment' => false,
                'catering' => false,
                'acceptanceFlow' => true,
                'capacity' => 10,
                'permissionBusinessPartner' => true,
                'permissionCitizen' => false,
                'permissionEmployee' => true,
                'hasWhitelist' => true,
                'displayName' => 'Dokk1 Lokale Test 2',
                'city' => 'Aarhus',
                'streetName' => 'Hack Kampmanns Pl. 2',
                'postalCode' => 8000,
                'resourceCategory' => 'Lokale',
                'formId' => null,
                'resourceImage' => 'https://placekitten.com/g/1000/1000',
                'resourceDisplayName' => 'Dokk1 Lokale Test 2',
                'locationDisplayName' => 'Location 1',
                'acceptConflict' => false,
            ],
            [
                'resourceEmail' => 'MSO-rolator-test1@aarhus.dk',
                'resourceName' => 'MSO-rolator-test1',
                'resourceDescription' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'resourceEmailText' => '',
                'location' => 'LOCATION2',
                'videoConferenceEquipment' => false,
                'wheelChairAccessible' => true,
                'monitorEquipment' => false,
                'catering' => false,
                'acceptanceFlow' => true,
                'capacity' => 10,
                'permissionBusinessPartner' => true,
                'permissionCitizen' => true,
                'permissionEmployee' => true,
                'displayName' => 'Test Rolator',
                'city' => 'Aarhus',
                'streetName' => 'En vej',
                'postalCode' => 8000,
                'resourceCategory' => 'Lokale',
                'formId' => 'http://selvbetjening.local.itkdev.dk/da/content/step-two-alt',
                'resourceImage' => 'https://placekitten.com/g/500/1000',
                'resourceDisplayName' => 'Test Rolator',
                'locationDisplayName' => 'Location 2',
                'acceptConflict' => false,
            ],
            [
                'resourceEmail' => 'without_location@bookaarhus.local.itkdev',
                'resourceName' => 'without_location',
                'resourceDescription' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'resourceEmailText' => '',
                'location' => '',
                'videoConferenceEquipment' => false,
                'wheelChairAccessible' => false,
                'monitorEquipment' => false,
                'catering' => false,
                'acceptanceFlow' => true,
                'capacity' => 5,
                'hasWhitelist' => false,
                'permissionBusinessPartner' => true,
                'permissionCitizen' => true,
                'permissionEmployee' => true,
                'displayName' => 'Test without location',
                'city' => 'Aarhus',
                'streetName' => 'En vej',
                'postalCode' => 8000,
                'resourceCategory' => 'Lokale',
                'formId' => null,
                'resourceImage' => 'https://placekitten.com/g/1000/500',
                'resourceDisplayName' => 'Test without location',
                'locationDisplayName' => null,
                'acceptConflict' => false,
            ],
            [
                'resourceEmail' => 'MSO-bil-test1@aarhus.dk',
                'resourceName' => 'MSO-bil-test1',
                'resourceDescription' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'resourceEmailText' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'location' => 'LOCATION_WHITELIST',
                'videoConferenceEquipment' => false,
                'wheelChairAccessible' => false,
                'monitorEquipment' => false,
                'catering' => false,
                'acceptanceFlow' => true,
                'hasWhitelist' => true,
                'capacity' => 5,
                'permissionBusinessPartner' => true,
                'permissionCitizen' => true,
                'permissionEmployee' => true,
                'displayName' => 'Test Bil',
                'city' => 'Aarhus V',
                'streetName' => 'En anden vej',
                'postalCode' => 8200,
                'resourceCategory' => 'Transport',
                'formId' => 'http://selvbetjening.local.itkdev.dk/da/content/step-two-alt',
                'resourceImage' => 'https://placekitten.com/g/800/600',
                'resourceDisplayName' => 'Test Bil',
                'locationDisplayName' => 'Location Whitelist',
                'acceptConflict' => true,
            ],
        ];

        foreach ($resources as $resource) {
            $res = new Resource();
            $res->setResourceMail($resource['resourceEmail']);
            $res->setResourceName($resource['resourceName']);
            $res->setResourceDescription($resource['resourceDescription']);
            $res->setResourceEmailText($resource['resourceEmailText']);
            $res->setLocation($resource['location']);
            $res->setWheelchairAccessible($resource['wheelChairAccessible']);
            $res->setVideoConferenceEquipment($resource['videoConferenceEquipment']);
            $res->setUpdateTimestamp(new \DateTime());
            $res->setMonitorEquipment($resource['monitorEquipment']);
            $res->setCatering($resource['catering']);
            $res->setAcceptanceFlow($resource['acceptanceFlow']);
            $res->setCapacity($resource['capacity']);
            $res->setPermissionBusinessPartner($resource['permissionBusinessPartner']);
            $res->setPermissionCitizen($resource['permissionCitizen']);
            $res->setPermissionEmployee($resource['permissionEmployee']);
            $res->setHasWhitelist($resource['hasWhitelist'] ?? false);
            $res->setDisplayName($resource['displayName'] ?? null);
            $res->setCity($resource['city'] ?? null);
            $res->setStreetName($resource['streetName'] ?? null);
            $res->setPostalCode($resource['postalCode'] ?? null);
            $res->setResourceCategory($resource['resourceCategory'] ?? null);
            $res->setFormId($resource['formId']);
            $res->setResourceImage($resource['resourceImage']);
            $res->setResourceDisplayName($resource['resourceDisplayName']);
            $res->setLocationDisplayName($resource['locationDisplayName']);
            $res->setAcceptConflict($resource['acceptConflict']);
            $manager->persist($res);
        }

        $manager->flush();

        $whitelistEntity = new CvrWhitelist();
        $whitelistEntity->setCvr(1234567890);
        $whitelistEntity->setResourceId($res->getId());
        $whitelistEntity->setUpdateTimestamp(new \DateTime());
        $manager->persist($whitelistEntity);

        $geoLocations = [
            '56.15357461749666, 10.214345916610233',
            '56.15408647075074, 10.199744795395857',
            '56.20302907626276, 10.181046014442838',
        ];

        for ($i = 0; $i < 1000; ++$i) {
            $res = new Resource();
            $res->setResourceMail("test$i@bookaarhus.local.itkdev");
            $res->setResourceName("test$i");
            $res->setResourceDescription('description');
            $res->setResourceEmailText('email text');
            $res->setLocation('NEW LOCATION');
            $res->setWheelchairAccessible(1 == random_int(0, 1));
            $res->setVideoConferenceEquipment(1 == random_int(0, 1));
            $res->setGeoCoordinates($geoLocations[random_int(0, 2)]);
            $res->setUpdateTimestamp(new \DateTime());
            $res->setMonitorEquipment(1 == random_int(0, 1));
            $res->setCatering(1 == random_int(0, 1));
            $res->setAcceptanceFlow(1 == random_int(0, 1));
            $res->setCapacity(random_int(1, 1000));
            $res->setPermissionBusinessPartner(1 == random_int(0, 1));
            $res->setPermissionCitizen(1 == random_int(0, 1));
            $res->setPermissionEmployee(1 == random_int(0, 1));
            $res->setHasWhitelist(false);
            $res->setDisplayName("Test $i");
            $res->setCity('Aarhus');
            $res->setStreetName('A random road');
            $res->setPostalCode(random_int(8000, 8400));
            $res->setResourceCategory(0 == random_int(0, 1) ? 'Lokale' : 'Transport');
            $res->setFormId(null);
            $res->setResourceImage(null);
            $res->setResourceDisplayName("Test $i");
            $res->setLocationDisplayName('New Location');
            $res->setAcceptConflict(false);
            $manager->persist($res);
        }

        $manager->flush();
    }
}
