<?php

namespace App\DataFixtures;

use App\Entity\Resources\AAKResource;
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
                'locationType' => 'LOCATION_TYPE_1',
                'type' => 'room',
                'wheelChairAccessible' => true,
                'videoConferenceEquipment' => false,
                'monitorEquipment' => false,
                'catering' => false,
                'acceptanceFlow' => true,
                'capacity' => 10,
            ],
            [
                'resourceEmail' => 'dokk1-lokale-test2@aarhus.dk',
                'resourceName' => 'dokk1-lokale-test2',
                'resourceDescription' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'resourceEmailText' => '',
                'location' => 'LOCATION1',
                'locationType' => 'LOCATION_TYPE_1',
                'type' => 'room',
                'wheelChairAccessible' => false,
                'videoConferenceEquipment' => true,
                'monitorEquipment' => false,
                'catering' => false,
                'acceptanceFlow' => true,
                'capacity' => 10,
            ],
            [
                'resourceEmail' => 'MSO-rolator-test1@aarhus.dk',
                'resourceName' => 'MSO-rolator-test1',
                'resourceDescription' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'resourceEmailText' => '',
                'location' => 'LOCATION2',
                'locationType' => 'LOCATION_TYPE_3',
                'type' => 'equipment',
                'videoConferenceEquipment' => false,
                'wheelChairAccessible' => true,
                'monitorEquipment' => false,
                'catering' => false,
                'acceptanceFlow' => true,
                'capacity' => 10,
            ],
            [
                'resourceEmail' => 'MSO-bil-test1@aarhus.dk',
                'resourceName' => 'MSO-bil-test1',
                'resourceDescription' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'resourceEmailText' => '',
                'location' => 'LOCATION2',
                'locationType' => 'LOCATION_TYPE_3',
                'type' => 'vehicle',
                'videoConferenceEquipment' => false,
                'wheelChairAccessible' => false,
                'monitorEquipment' => false,
                'catering' => false,
                'acceptanceFlow' => true,
                'capacity' => 5,
            ],
        ];

        foreach ($resources as $resource) {
            $res = new AAKResource();
            $res->setResourcemail($resource['resourceEmail']);
            $res->setResourcename($resource['resourceName']);
            $res->setResourcedescription($resource['resourceDescription']);
            $res->setResourceemailtext($resource['resourceEmailText']);
            $res->setLocation($resource['location']);
            $res->setLocationType($resource['locationType']);
            $res->setType($resource['type']);
            $res->setWheelchairaccessible($resource['wheelChairAccessible']);
            $res->setVideoconferenceequipment($resource['videoConferenceEquipment']);
            $res->setUpdatetimestamp(new \DateTime());
            $res->setMonitorequipment($resource['monitorEquipment']);
            $res->setCatering($resource['catering']);
            $res->setAcceptanceflow($resource['acceptanceFlow']);
            $res->setCapacity($resource['capacity']);
            $res->setBookingrights('');
            $manager->persist($res);
        }

        $manager->flush();
    }
}
