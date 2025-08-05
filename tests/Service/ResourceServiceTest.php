<?php

namespace App\Tests\Service;

use App\Repository\CvrWhitelistRepository;
use App\Repository\HolidayOpeningHoursRepository;
use App\Repository\LocationRepository;
use App\Repository\OpeningHoursRepository;
use App\Repository\ResourceRepository;
use App\Service\MetricsHelper;
use App\Service\ResourceService;
use App\Tests\AbstractBaseApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ResourceServiceTest extends AbstractBaseApiTestCase
{
    public function testGetAllResources(): void
    {
        $serializer = self::getContainer()->get(SerializerInterface::class);
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $cache = new ArrayAdapter(0, true, 0, 0);
        $client = self::getContainer()->get(HttpClientInterface::class);

        $metricsHelper = $this->createMock(MetricsHelper::class);
        $resourceRepository = self::getContainer()->get(ResourceRepository::class);
        $locationRepository = self::getContainer()->get(LocationRepository::class);
        $openingHoursRepository = self::getContainer()->get(OpeningHoursRepository::class);
        $holidayOpeningHoursRepository = self::getContainer()->get(HolidayOpeningHoursRepository::class);
        $cvrWhitelistRepository = self::getContainer()->get(CvrWhitelistRepository::class);

        $service = new ResourceService(
            $resourceRepository,
            $locationRepository,
            $openingHoursRepository,
            $holidayOpeningHoursRepository,
            $cache,
            $serializer,
            $metricsHelper,
            $client,
            $entityManager,
            '',
            '',
            '',
            '',
            '',
            $cvrWhitelistRepository,
            [],
        );

        $data = $service->getAllResources('citizen');

        $this->assertCount(2, $data);
        $this->assertEquals('DOKK1-Lokale-Test1', $data[0]->resourceName);

        $cacheEntry = $cache->get('resources-citizen', fn () => null);

        $this->assertNotNull($cacheEntry);

        $service->removeResourcesCacheEntry('citizen');

        $cacheEntry = $cache->get('resources-citizen', fn () => null);

        $this->assertNull($cacheEntry);
    }

    public function testGetWhitelistedResources(): void
    {
        $serializer = self::getContainer()->get(SerializerInterface::class);
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $cache = new ArrayAdapter(0, true, 0, 0);
        $client = self::getContainer()->get(HttpClientInterface::class);

        $metricsHelper = $this->createMock(MetricsHelper::class);
        $resourceRepository = self::getContainer()->get(ResourceRepository::class);
        $locationRepository = self::getContainer()->get(LocationRepository::class);
        $openingHoursRepository = self::getContainer()->get(OpeningHoursRepository::class);
        $holidayOpeningHoursRepository = self::getContainer()->get(HolidayOpeningHoursRepository::class);
        $cvrWhitelistRepository = self::getContainer()->get(CvrWhitelistRepository::class);

        $service = new ResourceService(
            $resourceRepository,
            $locationRepository,
            $openingHoursRepository,
            $holidayOpeningHoursRepository,
            $cache,
            $serializer,
            $metricsHelper,
            $client,
            $entityManager,
            '',
            '',
            '',
            '',
            '',
            $cvrWhitelistRepository,
            [],
        );

        $data = $service->getWhitelistedResources('businessPartner', '12345678');

        $this->assertCount(1, $data);
        $this->assertEquals('MSO-Rolator-test1', $data[0]->resourceName);
    }
}
