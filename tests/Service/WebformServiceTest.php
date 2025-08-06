<?php

namespace App\Tests\Service;

use App\Entity\Main\ApiKeyUser;
use App\Exception\WebformSubmissionRetrievalException;
use App\Message\WebformSubmitMessage;
use App\Repository\ApiKeyUserRepository;
use App\Service\MetricsHelper;
use App\Service\WebformService;
use App\Tests\AbstractBaseApiTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class WebformServiceTest extends AbstractBaseApiTestCase
{
    public function testGetData(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $client = $this->getMockBuilder(HttpClientInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request', 'withOptions', 'stream'])
            ->getMock();

        $testData = [
            'subject' => '123',
            'formElement' => 'booking_element',
            'resourceId' => '123',
            'start' => '123',
            'end' => '123',
            'name' => '123',
            'email' => '123',
            'userId' => '123',
            'userPermission' => '123',
        ];

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $response->method('toArray')->willReturn(
            [
                'fisk' => 'fusk',
            ],
            [
                'data' => [
                    'booking' => json_encode([
                        'subject' => 'subject',
                    ]),
                ],
            ],
            [
                'data' => [
                    'booking' => json_encode([
                        'subject' => 'subject',
                        'formElement' => 'booking_element',
                    ]),
                ],
            ],
            [
                'data' => [
                    'booking' => json_encode($testData),
                    'otherStuff' => ['test1, test2'],
                ],
            ],
            [
                'data' => [
                    'booking' => json_encode([
                        'formElement' => 'booking_element',
                    ]),
                ],
            ],
            [
                'data' => [
                    'booking' => json_encode([
                        'formElement' => 'booking_element',
                        'subject' => 'subject',
                    ]),
                ],
            ],
            [
                'data' => [
                    'booking' => json_encode([
                        'formElement' => 'booking_element',
                        'subject' => 'subject',
                        'resourceId' => '123',
                    ]),
                ],
            ],
            [
                'data' => [
                    'booking' => json_encode([
                        'formElement' => 'booking_element',
                        'subject' => 'subject',
                        'resourceId' => '123',
                        'start' => '123',
                    ]),
                ],
            ],
            [
                'data' => [
                    'booking' => json_encode([
                        'formElement' => 'booking_element',
                        'subject' => 'subject',
                        'resourceId' => '123',
                        'start' => '123',
                        'end' => '123',
                    ]),
                ],
            ],
            [
                'data' => [
                    'booking' => json_encode([
                        'formElement' => 'booking_element',
                        'subject' => 'subject',
                        'resourceId' => '123',
                        'start' => '123',
                        'end' => '123',
                        'name' => '123',
                    ]),
                ],
            ],
            [
                'data' => [
                    'booking' => json_encode([
                        'formElement' => 'booking_element',
                        'subject' => 'subject',
                        'resourceId' => '123',
                        'start' => '123',
                        'end' => '123',
                        'name' => '123',
                        'email' => '123',
                    ]),
                ],
            ],
            [
                'data' => [
                    'booking' => json_encode([
                        'formElement' => 'booking_element',
                        'subject' => 'subject',
                        'resourceId' => '123',
                        'start' => '123',
                        'end' => '123',
                        'name' => '123',
                        'email' => '123',
                        'userId' => '123',
                    ]),
                ],
            ],
            [
                'data' => [
                    'booking' => json_encode([
                        'formElement' => 'booking_element',
                        'subject' => 'subject',
                        'resourceId' => '123',
                        'start' => '123',
                        'end' => '123',
                        'name' => '123',
                        'email' => '123',
                        'userId' => '123',
                        'userPermission' => '123',
                    ]),
                ],
            ],
        );

        $client->method('request')->willReturn($response);

        $repo = $this->getMockBuilder(ApiKeyUserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find'])
            ->getMock();

        $user = new ApiKeyUser();
        $user->setWebformApiKey('webformapikey');
        $repo->method('find')->willReturn($user);

        $metric = $this->createMock(MetricsHelper::class);

        $service = new WebformService($client, $logger, $repo, $metric);

        $message = new WebformSubmitMessage(
            '1234',
            '12345',
            'test',
            'http://localhost/test/1234',
            1
        );

        $errorMessage = null;
        try {
            $service->getData($message);
        } catch (WebformSubmissionRetrievalException $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals('Webform data not set', $errorMessage);

        $errorMessage = null;
        try {
            $service->getData($message);
        } catch (WebformSubmissionRetrievalException $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals('No submission data found.', $errorMessage);

        $errorMessage = null;
        try {
            $service->getData($message);
        } catch (WebformSubmissionRetrievalException $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals('Webform (booking) resourceId not set', $errorMessage);

        $data = $service->getData($message);
        $this->assertEquals([
            'bookingData' => [
                'booking' => $testData,
            ],
            'metaData' => [
                'otherStuff' => 'test1, test2',
            ],
        ], $data);

        // Subject not set.
        $errorMessage = null;
        try {
            $service->getData($message);
        } catch (WebformSubmissionRetrievalException $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals('Webform (booking) subject not set', $errorMessage);

        // resourceId not set.
        $errorMessage = null;
        try {
            $service->getData($message);
        } catch (WebformSubmissionRetrievalException $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals('Webform (booking) resourceId not set', $errorMessage);

        // start not set.
        $errorMessage = null;
        try {
            $service->getData($message);
        } catch (WebformSubmissionRetrievalException $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals('Webform (booking) start not set', $errorMessage);

        // end not set.
        $errorMessage = null;
        try {
            $service->getData($message);
        } catch (WebformSubmissionRetrievalException $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals('Webform (booking) end not set', $errorMessage);

        // name not set.
        $errorMessage = null;
        try {
            $service->getData($message);
        } catch (WebformSubmissionRetrievalException $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals('Webform (booking) name not set', $errorMessage);

        // email not set.
        $errorMessage = null;
        try {
            $service->getData($message);
        } catch (WebformSubmissionRetrievalException $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals('Webform (booking) email not set', $errorMessage);

        // userId not set.
        $errorMessage = null;
        try {
            $service->getData($message);
        } catch (WebformSubmissionRetrievalException $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals('Webform (booking) userId not set', $errorMessage);

        // userPermission not set.
        $errorMessage = null;
        try {
            $service->getData($message);
        } catch (WebformSubmissionRetrievalException $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals('Webform (booking) userPermission not set', $errorMessage);
    }

    public function testSortWebformSubmissionDataByType(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $client = $this->createMock(HttpClientInterface::class);
        $repo = $this->createMock(ApiKeyUserRepository::class);
        $metric = $this->createMock(MetricsHelper::class);

        $service = new WebformService($client, $logger, $repo, $metric);

        $result = $service->sortWebformSubmissionDataByType(['data' => [
            'test1' => 'test2',
        ],
        ]);

        $this->assertEquals([
            'bookingData' => [],
            'arrayData' => [],
            'stringData' => [
                'test1' => 'test2',
            ],
        ], $result);
    }

    public function testGetWebformSubmission(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $client = $this->getMockBuilder(HttpClientInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request', 'withOptions', 'stream'])
            ->getMock();

        $client->method('request')->willThrowException(new TransportException('Transport error'));

        $repo = $this->createMock(ApiKeyUserRepository::class);

        $metric = $this->createMock(MetricsHelper::class);

        $service = new WebformService($client, $logger, $repo, $metric);

        $errorMessage = null;
        try {
            $service->getWebformSubmission('http://localhost', '1234');
        } catch (WebformSubmissionRetrievalException $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals('Transport error', $errorMessage);
    }

    public function testNoUser(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $client = $this->createMock(HttpClientInterface::class);
        $repo = $this->getMockBuilder(ApiKeyUserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find'])
            ->getMock();
        $repo->method('find')->willReturn(null);

        $metric = $this->createMock(MetricsHelper::class);

        $service = new WebformService($client, $logger, $repo, $metric);

        $message = new WebformSubmitMessage(
            '1234',
            '12345',
            'test',
            'http://localhost/test/1234',
            1
        );

        $errorMessage = null;
        try {
            $service->getData($message);
        } catch (WebformSubmissionRetrievalException $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals('ApiKeyUser not set.', $errorMessage);
    }
}
