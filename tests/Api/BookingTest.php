<?php

namespace App\Tests\Api;

use App\Entity\Main\ApiKeyUser;
use App\Entity\Main\Booking;
use App\Entity\Resources\AAKResource;
use App\Message\CreateBookingMessage;
use App\Message\WebformSubmitMessage;
use App\MessageHandler\CreateBookingHandler;
use App\MessageHandler\WebformSubmitHandler;
use App\Repository\Resources\AAKResourceRepository;
use App\Repository\Resources\CvrWhitelistRepository;
use App\Security\Voter\BookingVoter;
use App\Service\CreateBookingService;
use App\Service\MetricsHelper;
use App\Service\MicrosoftGraphBookingService;
use App\Service\NotificationServiceInterface;
use App\Service\WebformService;
use App\Tests\AbstractBaseApiTestCase;
use App\Utils\ValidationUtils;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class BookingTest extends AbstractBaseApiTestCase
{
    use InteractsWithMessenger;

    /**
     * @throws TransportExceptionInterface|\Exception
     */
    public function testBookingVoter(): void
    {
        $res = new AAKResource();
        $res->setResourceMail('test@bookaarhus.local.itkdev.dk');
        $res->setResourceName('test');
        $res->setPermissionBusinessPartner(true);
        $res->setPermissionCitizen(false);

        $container = self::getContainer();
        $security = $container->get(Security::class);

        $aakResourceRepositoryMock = $this->getMockBuilder(AAKResourceRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findOneByEmail'])
            ->getMock();
        $aakResourceRepositoryMock->expects($this->exactly(2))->method('findOneByEmail')->willReturn($res);

        $container->set(AAKResourceRepository::class, $aakResourceRepositoryMock);

        $booking = new Booking();
        $booking->setResourceEmail('test@bookaarhus.local.itkdev.dk');
        $booking->setUserId('');
        $booking->setUserPermission(BookingVoter::PERMISSION_BUSINESS_PARTNER);

        $this->assertEquals(false, $security->isGranted(BookingVoter::CREATE, $booking));

        $booking = new Booking();
        $booking->setResourceEmail('test@bookaarhus.local.itkdev.dk');
        $booking->setUserId('1234567890');
        $booking->setUserPermission(BookingVoter::PERMISSION_BUSINESS_PARTNER);

        $this->assertEquals(true, $security->isGranted(BookingVoter::CREATE, $booking));

        $booking = new Booking();
        $booking->setResourceEmail('test@bookaarhus.local.itkdev.dk');
        $booking->setUserId('1234567890');
        $booking->setUserPermission(BookingVoter::PERMISSION_CITIZEN);

        $this->assertEquals(false, $security->isGranted(BookingVoter::CREATE, $booking));
    }

    public function testBookingWebform(): void
    {
        $this->transport('async')->queue()->assertEmpty();

        $client = $this->getAuthenticatedClient();

        $requestData = [
            'data' => [
                'webform' => [
                    'id' => 'booking',
                ],
                'submission' => [
                    'uuid' => '795f5a1c-a0ac-4f8a-8834-bb71fca8585d',
                ],
            ],
            'links' => [
                'sender' => 'https://bookaarhus.local.itkdev.dk',
                'get_submission_url' => 'https://bookaarhus.local.itkdev.dk/webform_rest/booking/submission/123123123',
            ],
        ];

        $client->request('POST', '/v1/bookings-webform', [
            'json' => $requestData,
        ]);

        $this->assertResponseStatusCodeSame(201);

        /** @var WebformSubmitMessage $message */
        $message = $this->transport('async')->queue()->first(WebformSubmitMessage::class)->getMessage();
        $this->assertEquals('booking', $message->getWebformId());
        $this->assertEquals('795f5a1c-a0ac-4f8a-8834-bb71fca8585d', $message->getSubmissionUuid());
        $this->assertEquals('https://bookaarhus.local.itkdev.dk', $message->getSender());
        $this->assertEquals('https://bookaarhus.local.itkdev.dk/webform_rest/booking/submission/123123123', $message->getSubmissionUrl());
        $this->assertEquals(1, $message->getApiKeyUserId());

        $this->transport('async')->queue()->assertCount(1);
        $this->transport('async')->queue()->assertContains(WebformSubmitMessage::class);
    }

    /**
     * @throws \Exception
     */
    public function testWebformSubmitMessageHandler(): void
    {
        $this->transport('async')->queue()->assertEmpty();

        $webformServiceMock = $this->getMockBuilder(WebformService::class)
            ->onlyMethods(['getWebformSubmission', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $webformServiceMock->method('getWebformSubmission')->willReturn([
            'data' => [
                'booking1' => json_encode([
                    'subject' => 'test1',
                    'resourceId' => 'test@bookaarhus.local.itkdev.dk',
                    'start' => '2022-08-18T10:00:00.000Z',
                    'end' => '2022-08-18T10:30:00.000Z',
                    'userId' => 'test4',
                    'userPermission' => 'citizen',
                    'formElement' => 'booking_element',
                    'name' => 'auther1',
                    'email' => 'author1@bookaarhus.local.itkdev.dk',
                ]),
                'meta_data_1' => 'This is a metadata field',
                'meta_data_2' => 'This is also metadata',
                'meta_data_3' => 'Lorem ipsum metadata',
                'meta_data_4' => ['a' => 1, 'b' => 2, 'c' => 3],
                'meta_data_5' => ['a1', 'b2', 'c3'],
            ],
        ]);

        $webformServiceMock->method('getData')->willReturn([
            'bookingData' => [
                'booking1' => [
                    'subject' => 'test1',
                    'resourceId' => 'test@bookaarhus.local.itkdev.dk',
                    'start' => '2022-08-18T10:00:00.000Z',
                    'end' => '2022-08-18T10:30:00.000Z',
                    'userId' => 'test4',
                    'userPermission' => 'citizen',
                    'formElement' => 'booking_element',
                    'name' => 'auther1',
                    'email' => 'author1@bookaarhus.local.itkdev.dk',
                ],
            ],
            'metaData' => [
                'meta_data_4' => '1, 2, 3',
                'meta_data_5' => 'a1, b2, c3',
                'meta_data_1' => 'This is a metadata field',
                'meta_data_2' => 'This is also metadata',
                'meta_data_3' => 'Lorem ipsum metadata',
            ],
        ]);

        $validationUtilsMock = $this->getMockBuilder(ValidationUtils::class)
            ->onlyMethods(['validateEmail', 'validateDate'])
            ->disableOriginalConstructor()
            ->getMock();

        $validationUtilsMock->method('validateDate')->willReturn(new \DateTime('2022-08-18T10:00:00.000Z'));
        $validationUtilsMock->method('validateEmail')->willReturn('test@bookaarhus.local.itkdev.dk');

        $logger = $this->createMock(LoggerInterface::class);
        $metric = $this->createMock(MetricsHelper::class);

        $container = self::getContainer();
        $bus = $container->get(MessageBusInterface::class);
        $createBookingService = $container->get(CreateBookingService::class);

        $aakBookingRepository = $this->getMockBuilder(AAKResourceRepository::class)
            ->onlyMethods(['findOneBy'])
            ->disableOriginalConstructor()
            ->getMock();
        $resource = new AAKResource();
        $resource->setResourceName('DOKK1-Lokale-Test1');
        $resource->setResourceDisplayName('DOKK1 Lokale Test1');
        $resource->setResourceMail('DOKK1-Lokale-Test1@aarhus.dk');
        $resource->setLocation('Dokk1');
        $aakBookingRepository->method('findOneBy')->willReturn($resource);

        $entityManager = self::getContainer()->get('doctrine')->getManager();

        /** @var ApiKeyUser $testUser */
        $testUser = $entityManager->getRepository(ApiKeyUser::class)->findOneBy(['name' => 'test']);

        $webformSubmitHandler = new WebformSubmitHandler($webformServiceMock, $bus, $validationUtilsMock, $logger, $aakBookingRepository, $metric, $createBookingService);

        $webformSubmitHandler->__invoke(new WebformSubmitMessage(
            'booking',
            '795f5a1c-a0ac-4f8a-8834-bb71fca8585d',
            'https://bookaarhus.local.itkdev.dk',
            'https://bookaarhus.local.itkdev.dk/webform_rest/booking/submission/123123123',
            $testUser->getId()
        ));

        $this->transport('async')->queue()->assertContains(CreateBookingMessage::class);
        $this->transport('async')->queue()->assertCount(1);
    }

    public function testCreateBookingHandler(): void
    {
        $microsoftGraphServiceMock = $this->getMockBuilder(MicrosoftGraphBookingService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createBookingForResource'])
            ->getMock();
        $microsoftGraphServiceMock->expects($this->exactly(1))->method('createBookingForResource')->willReturn([
            'iCalUId' => '12345',
        ]);

        $container = self::getContainer();
        $logger = $container->get(LoggerInterface::class);
        $bus = $container->get(MessageBusInterface::class);

        $booking = new Booking();
        $booking->setBody('test');
        $booking->setSubject('test');
        $booking->setResourceName('test');
        $booking->setResourceEmail('test@bookaarhus.local.itkdev.dk');
        $booking->setStartTime(new \DateTime());
        $booking->setEndTime(new \DateTime());
        $booking->setUserName('author1');
        $booking->setUserMail('author1@bookaarhus.local.itkdev.dk');
        $booking->setMetaData([
            'meta_data_4' => '1, 2, 3',
            'meta_data_5' => 'a1, b2, c3',
            'meta_data_1' => 'This is a metadata field',
            'meta_data_2' => 'This is also metadata',
            'meta_data_3' => 'Lorem ipsum metadata',
        ]);
        $booking->setUserPermission(BookingVoter::PERMISSION_CITIZEN);
        $booking->setUserId('1234567890');

        $res = new AAKResource();
        $res->setResourceMail('test@bookaarhus.local.itkdev.dk');
        $res->setResourceName('test');
        $res->setResourceDescription('desc');
        $res->setResourceEmailText('emailtext');
        $res->setLocation('LOCATION1');
        $res->setWheelchairAccessible(true);
        $res->setVideoConferenceEquipment(false);
        $res->setUpdateTimestamp(new \DateTime());
        $res->setMonitorEquipment(false);
        $res->setCatering(false);
        $res->setAcceptanceFlow(false);
        $res->setCapacity(10);
        $res->setPermissionBusinessPartner(true);
        $res->setPermissionCitizen(true);
        $res->setPermissionEmployee(true);
        $res->setHasWhitelist(false);
        $res->setAcceptConflict(false);

        $aakResourceRepositoryMock = $this->getMockBuilder(AAKResourceRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findOneByEmail'])
            ->getMock();
        $aakResourceRepositoryMock->expects($this->exactly(2))->method('findOneByEmail')->willReturn($res);

        $security = $container->get(Security::class);

        $container->set(AAKResourceRepository::class, $aakResourceRepositoryMock);

        $notificationServiceMock = $this->createMock(NotificationServiceInterface::class);
        $container->set(NotificationServiceInterface::class, $notificationServiceMock);

        $cvrWhitelistRepositoryMock = $this->createMock(CvrWhitelistRepository::class);

        $metric = $this->createMock(MetricsHelper::class);

        $createBookingHandler = new CreateBookingHandler($microsoftGraphServiceMock, $logger, $aakResourceRepositoryMock, $security, $bus, $cvrWhitelistRepositoryMock, $metric);
        $createBookingHandler->__invoke(new CreateBookingMessage($booking));
    }

    public function testInvalidBookingWebform(): void
    {
        $this->transport('async')->queue()->assertEmpty();

        $client = $this->getAuthenticatedClient();

        $requestData = [
            'data' => [
                'webform' => [
                    'id' => 'booking',
                ],
                'submission' => [
                    'uuid' => '795f5a1c-a0ac-4f8a-8834-bb71fca8585d',
                ],
            ],
        ];

        $client->request('POST', '/v1/bookings-webform', [
            'json' => $requestData,
        ]);

        $this->assertResponseStatusCodeSame(400);

        $this->transport('async')->queue()->assertCount(0);
    }
}
