<?php

namespace App\DataFixtures;

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
