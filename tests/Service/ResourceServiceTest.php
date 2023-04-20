<?php

namespace App\Tests\Service;

use App\Repository\Resources\AAKResourceRepository;
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

        $aakResourceRepository = $this->getMockBuilder(AAKResourceRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllByPermission'])
            ->getMock();
        $aakResourceRepository->method('getAllByPermission')->willReturn([
            NotificationServiceData::getResource(),
        ]);

        $service = new ResourceService($aakResourceRepository, $cache, $serializer);

        $data = $service->getAllResources('citizen');

        $this->assertCount(1, $data);
        $this->assertEquals('DOKK1-Lokale-Test1', $data[0]->resourceName);

        $cacheEntry = $cache->get('resources-citizen', function () { return null; });

        $this->assertNotNull($cacheEntry);

        $service->removeResourcesCacheEntry('citizen');

        $cacheEntry = $cache->get('resources-citizen', function () { return null; });

        $this->assertNull($cacheEntry);
    }

    public function testGetWhitelistedResources(): void
    {
        $serializer = self::getContainer()->get(SerializerInterface::class);
        $cache = new ArrayAdapter(0, true, 0, 0);

        $aakResourceRepository = $this->getMockBuilder(AAKResourceRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOnlyWhitelisted'])
            ->getMock();
        $aakResourceRepository->method('getOnlyWhitelisted')->willReturn([
            NotificationServiceData::getResource(),
        ]);

        $service = new ResourceService($aakResourceRepository, $cache, $serializer);

        $data = $service->getWhitelistedResources('citizen', 'key');

        $this->assertCount(1, $data);
        $this->assertEquals('DOKK1-Lokale-Test1', $data[0]->resourceName);
    }
}
