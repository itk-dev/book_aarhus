<?php

namespace App\Tests\Service;

use App\Repository\ResourceRepository;
use App\Service\MetricsHelper;
use App\Service\ResourceService;
use App\Tests\AbstractBaseApiTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Serializer\SerializerInterface;

class ResourceServiceTest extends AbstractBaseApiTestCase
{
    public function testGetAllResources(): void
    {
        $serializer = self::getContainer()->get(SerializerInterface::class);
        $cache = new ArrayAdapter(0, true, 0, 0);

        $aakResourceRepository = $this->getMockBuilder(ResourceRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllByPermission'])
            ->getMock();
        $aakResourceRepository->method('getAllByPermission')->willReturn([
            NotificationServiceData::getResource(),
        ]);

        $metric = $this->createMock(MetricsHelper::class);

        $service = new ResourceService($aakResourceRepository, $cache, $serializer, $metric);

        $data = $service->getAllResources('citizen');

        $this->assertCount(1, $data);
        $this->assertEquals('DOKK1-Lokale-Test1', $data[0]->resourceName);

        $cacheEntry = $cache->get('resources-citizen', fn() => null);

        $this->assertNotNull($cacheEntry);

        $service->removeResourcesCacheEntry('citizen');

        $cacheEntry = $cache->get('resources-citizen', fn() => null);

        $this->assertNull($cacheEntry);
    }

    public function testGetWhitelistedResources(): void
    {
        $serializer = self::getContainer()->get(SerializerInterface::class);
        $cache = new ArrayAdapter(0, true, 0, 0);

        $aakResourceRepository = $this->getMockBuilder(ResourceRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOnlyWhitelisted'])
            ->getMock();
        $aakResourceRepository->method('getOnlyWhitelisted')->willReturn([
            NotificationServiceData::getResource(),
        ]);

        $metric = $this->createMock(MetricsHelper::class);

        $service = new ResourceService($aakResourceRepository, $cache, $serializer, $metric);

        $data = $service->getWhitelistedResources('citizen', 'key');

        $this->assertCount(1, $data);
        $this->assertEquals('DOKK1-Lokale-Test1', $data[0]->resourceName);
    }
}
