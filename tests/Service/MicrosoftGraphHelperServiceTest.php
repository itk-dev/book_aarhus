<?php

namespace App\Tests\Service;

use App\Exception\MicrosoftGraphCommunicationException;
use App\Factory\ClientFactory;
use App\Service\MicrosoftGraphHelperService;
use App\Tests\AbstractBaseApiTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class MicrosoftGraphHelperServiceTest extends AbstractBaseApiTestCase
{
    public function testAuthenticateAsServiceAccount(): void
    {
        $cache = new ArrayAdapter(0, true, 0, 0);

        $guzzleClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['post'])
            ->getMock();
        $response = new Response(200, [], json_encode(['access_token' => '123']));
        $guzzleClient->method('post')->willReturn($response);

        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getGuzzleClient'])
            ->getMock();
        $clientFactory->method('getGuzzleClient')->willReturn($guzzleClient);

        $service = new MicrosoftGraphHelperService('', '', '', '', $cache, $clientFactory);

        $token = $service->authenticateAsServiceAccount();

        $this->assertEquals('123', $token);

        $response = new Response(200, [], 'test');
        $guzzleClient->method('post')->willReturn($response);

        $errorState = false;
        try {
            $service->authenticateAsUser('123', '123');
        } catch (MicrosoftGraphCommunicationException) {
            $errorState = true;
        }
        $this->assertEquals(true, $errorState);
    }
}
