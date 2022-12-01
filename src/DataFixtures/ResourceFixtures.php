<?php

namespace App\DataFixtures;

use App\Entity\Resources\AAKResource;
use App\Entity\Resources\CvrWhitelist;
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
                'resourceCategory' => 'Lokaler',
                'formId' => NULL
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
                'resourceCategory' => 'Lokaler',
                'formId' => NULL
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
                'permissionBusinessPartner' => false,
                'permissionCitizen' => true,
                'permissionEmployee' => true,
                'displayName' => 'Test Rolator',
                'city' => 'Aarhus',
                'streetName' => 'En vej',
                'postalCode' => 8000,
                'resourceCategory' => 'Hjælpemidler',
                'formId' => 'http://selvbetjening.local.itkdev.dk/da/content/step-two-alt'
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
                'resourceCategory' => 'Lokaler',
                'formId' => NULL
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
                'resourceCategory' => 'Køretøjer',
                'formId' => 'http://selvbetjening.local.itkdev.dk/da/content/step-two-alt'
            ],
        ];

        foreach ($resources as $resource) {
            $res = new AAKResource();
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
            $res = new AAKResource();
            $res->setResourceMail("test$i@bookaarhus.local.itkdev");
            $res->setResourceName("test$i");
            $res->setResourceDescription('description');
            $res->setResourceEmailText('email text');
            $res->setLocation('NEW LOCATION');
            $res->setWheelchairAccessible(1 == rand(0, 1));
            $res->setVideoConferenceEquipment(1 == rand(0, 1));
            $res->setGeoCoordinates($geoLocations[rand(0, 2)]);
            $res->setUpdateTimestamp(new \DateTime());
            $res->setMonitorEquipment(1 == rand(0, 1));
            $res->setCatering(1 == rand(0, 1));
            $res->setAcceptanceFlow(1 == rand(0, 1));
            $res->setCapacity(rand(1, 1000));
            $res->setPermissionBusinessPartner(1 == rand(0, 1));
            $res->setPermissionCitizen(1 == rand(0, 1));
            $res->setPermissionEmployee(1 == rand(0, 1));
            $res->setHasWhitelist(false);
            $res->setDisplayName("Test $i");
            $res->setCity('Aarhus');
            $res->setStreetName('A random road');
            $res->setPostalCode(rand(8000, 8400));
            $res->setResourceCategory(0 == rand(0, 1) ? 'Lokaler' : 'Køretøjer');
            $res->setFormId(NULL);
            $manager->persist($res);
        }

        $manager->flush();
    }
}
