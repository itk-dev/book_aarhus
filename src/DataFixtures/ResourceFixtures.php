<?php

namespace App\DataFixtures;

use App\Entity\Main\ApiKeyUser;
use App\Service\ResourceService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ResourceFixtures extends Fixture
{
    public function __construct(private readonly ResourceService $resourceService)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $apiKey = new ApiKeyUser();
        $apiKey->setApiKey('ef2bf626d331e7e9543b5877a3ba7c37913b0ee7a1b975d033f653e1f0d52d215451f976c0d6006518fbc0fcbda12280b6b4e7a90535138fb79bcc3686e2653e');
        $apiKey->setWebformApiKey('1234567890');
        $apiKey->setName('fixtureApiKey');
        $manager->persist($apiKey);
        $manager->flush();

        $locations = json_decode(file_get_contents('public/fixtures/resources/locations.json'), true, 512, JSON_THROW_ON_ERROR);
        $this->resourceService->updateLocations($locations);

        $resources = json_decode(file_get_contents('public/fixtures/resources/resources.json'), true, 512, JSON_THROW_ON_ERROR);
        $this->resourceService->updateResources($resources);

        $whitelist = json_decode(file_get_contents('public/fixtures/resources/cvr_whitelist.json'), true, 512, JSON_THROW_ON_ERROR);
        $this->resourceService->updateCVRWhitelists($whitelist);

        $openingHours = json_decode(file_get_contents('public/fixtures/resources/opening_hours.json'), true, 512, JSON_THROW_ON_ERROR);
        $this->resourceService->updateOpeningHours($openingHours);

        $holidayOpeningHours = json_decode(file_get_contents('public/fixtures/resources/holiday_opening_hours.json'), true, 512, JSON_THROW_ON_ERROR);
        $this->resourceService->updateHolidayOpeningHours($holidayOpeningHours);
    }
}
