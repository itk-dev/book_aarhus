<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Service\MicrosoftGraphService;
use Microsoft\Graph\Http\GraphRequest;
use Microsoft\Graph\Http\GraphResponse;

class BusyIntervalTest extends ApiTestCase
{
    public static function setUpBeforeClass(): void
    {
        static::bootKernel();
    }

    public function testNoFilters(): void
    {
        $client = static::createClient();

        $client->request('GET', '/v1/busy-intervals?page=1', ['headers' => ['Content-Type' => 'application/ld+json']]);
        $this->assertResponseStatusCodeSame(400);
    }

    public function testValidRequest(): void
    {
        $client = static::createClient();
        $container = self::getContainer();

        $microsoftGraphServiceMock = $this->getMockBuilder(MicrosoftGraphService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request', 'authenticateAsServiceAccount'])
            ->getMock();

        $microsoftGraphServiceMock->method('authenticateAsServiceAccount')->willReturn('1234');

        $microsoftGraphServiceMock->method('request')->willReturn(
            new GraphResponse(
                new GraphRequest('GET', '/', '123', 'http://localhost', 'v1'),
                json_encode([
                    'value' => [
                        [
                            'scheduleId' => 'resource@example.com',
                            'scheduleItems' => [
                                [
                                    'start' => [
                                        'dateTime' => '2019-03-15T09:00:00',
                                        'timeZone' => 'UTC',
                                    ],
                                    'end' => [
                                        'dateTime' => '2019-03-15T11:00:00',
                                        'timeZone' => 'UTC',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
            )
        );

        $container->set('App\Service\MicrosoftGraphServiceInterface', $microsoftGraphServiceMock);

        $url = '/v1/busy-intervals?resources=resource%40example.com&dateStart=2022-05-30T17%3A32%3A28Z&dateEnd=2022-06-22T17%3A32%3A28Z&page=1';

        $client->request('GET', $url, ['headers' => ['Content-Type' => 'application/ld+json']]);

        $this->assertResponseStatusCodeSame(200);

        $this->assertJsonContains([
            '@context' => '/contexts/BusyInterval',
            '@type' => 'hydra:Collection',
            'hydra:member' => [
                [
                    'resource' => 'resource@example.com',
                    'startTime' => '2019-03-15T09:00:00+00:00',
                    'endTime' => '2019-03-15T11:00:00+00:00',
                ],
            ],
        ]);
    }
}
