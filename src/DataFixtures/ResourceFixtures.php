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
                'acceptanceFlow' => true,
                'capacity' => 10,
                'permissionBusinessPartner' => true,
                'permissionCitizen' => true,
                'permissionEmployee' => true,
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
            ],
            [
                'resourceEmail' => 'MSO-bil-test1@aarhus.dk',
                'resourceName' => 'MSO-bil-test1',
                'resourceDescription' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'resourceEmailText' => '',
                'location' => 'LOCATION2',
                'videoConferenceEquipment' => false,
                'wheelChairAccessible' => false,
                'monitorEquipment' => false,
                'catering' => false,
                'acceptanceFlow' => true,
                'capacity' => 5,
                'permissionBusinessPartner' => true,
                'permissionCitizen' => true,
                'permissionEmployee' => true,
            ],
            [
                'resourceEmail' => 'whitelist@bookaarhus.local.itkdev',
                'resourceName' => 'whitelist',
                'resourceDescription' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'resourceEmailText' => '',
                'location' => 'LOCATION_WHITELIST',
                'videoConferenceEquipment' => false,
                'wheelChairAccessible' => false,
                'monitorEquipment' => false,
                'catering' => false,
                'acceptanceFlow' => true,
                'capacity' => 5,
                'hasWhitelist' => true,
                'permissionBusinessPartner' => true,
                'permissionCitizen' => false,
                'permissionEmployee' => false,
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
            $manager->persist($res);
        }

        $manager->flush();

        $whitelistEntity = new CvrWhitelist();
        $whitelistEntity->setCvr('1234567890');
        $whitelistEntity->setResourceId($res->getId());
        $whitelistEntity->setUpdateTimestamp(new \DateTime());
        $manager->persist($whitelistEntity);

        $manager->flush();
    }
}
