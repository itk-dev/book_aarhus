<?php

namespace App\Entity\Main;

use ApiPlatform\Symfony\Action\NotFoundAction;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Controller\CreateBookingController;
use App\Controller\CreateBookingWebformSubmitController;
use App\Dto\CreateBookingsInput;
use App\Dto\WebformBookingInput;
use Symfony\Component\Uid\Ulid;
use ApiPlatform\OpenApi\Model;


#[ApiResource(operations: [
    new Get(
        controller: NotFoundAction::class,
        openapiContext: ['description' => 'unsupported action', 'summary' => 'unsupported action'],
        output: false,
        read: false
    ),
    new Post(
        uriTemplate: '/bookings-webform',
        controller: CreateBookingWebformSubmitController::class,
        openapiContext: [
            'description' => 'Create a booking from a Drupal Webform submit.',
            'summary' => 'Create a booking from a Drupal Webform submit.',
            'operationId' => 'post-v1-create-booking-webform-submit',
            'parameters' => [],
            'response' => [
                '200' => [
                    'description' => 'Created',
                ],
            ],
        ],
        input: WebformBookingInput::class
    ),
    new Post(
        uriTemplate: '/bookings',
        controller: CreateBookingController::class,
        openapi: new Model\Operation(
            description: 'Create bookings',
            requestBody: new Model\RequestBody(
                content: new \ArrayObject([
                    'application/json' => [
                        'schema' => [
                            'type' => 'array',
                            'properties' => [
                                'abortIfAnyFail' => 'bool',
                                'bookings' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'subject' => 'string',
                                        'start' => 'string',
                                        'end' => 'string',
                                        'name' => 'string',
                                        'email' => 'string',
                                        'resourceId' => 'string',
                                        'clientBookingId' => 'string',
                                        'userId' => 'string',
                                        'metaData' => 'array',
                                    ]
                                ],
                            ]
                        ],
                        'example' => [
                            'bookings' => [
                                [
                                    'subject' => 'Test Booking',
                                    'start' => '2004-02-26T15:00:00+00:00',
                                    'end' => '2004-02-26T15:30:00+00:00',
                                    'name' => 'Admin Jensen',
                                    'email' => 'admin_jensen@example.com',
                                    'resourceId' => 'test@example.com',
                                    'clientBookingId' => '1234567890',
                                    'userId' => 'some_unqiue_user_id',
                                    'metaData' => [
                                        'data1' => 'example1',
                                        'data2' => 'example2'
                                    ],
                                ]
                            ]
                        ]
                    ]
                ])
            )
        ),
        input: CreateBookingsInput::class
    ),
])]
class Booking
{
    #[ApiProperty(identifier: true)]
    private string $id;

    private string $resourceEmail;

    private string $resourceName;

    private string $subject;

    private string $body;

    private \DateTime $startTime;

    private \DateTime $endTime;

    private string $userMail;

    private string $userName;

    private array $metaData;

    private string $userId;

    private string $userPermission;

    private ?string $whitelistKey;

    public function __construct()
    {
        $this->id = Ulid::generate();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getResourceEmail(): string
    {
        return $this->resourceEmail;
    }

    public function setResourceEmail(string $resourceEmail): void
    {
        $this->resourceEmail = $resourceEmail;
    }

    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    public function setResourceName(string $resourceName): void
    {
        $this->resourceName = $resourceName;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getStartTime(): \DateTime
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTime $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getEndTime(): \DateTime
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTime $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function getUserMail(): string
    {
        return $this->userMail;
    }

    public function setUserMail(string $userMail): void
    {
        $this->userMail = $userMail;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): void
    {
        $this->userName = $userName;
    }

    public function getMetaData(): array
    {
        return $this->metaData;
    }

    public function setMetaData(array $metaData): void
    {
        $this->metaData = $metaData;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getUserPermission(): string
    {
        return $this->userPermission;
    }

    public function setUserPermission(string $userPermission): void
    {
        $this->userPermission = $userPermission;
    }

    public function getWhitelistKey(): ?string
    {
        return $this->whitelistKey;
    }

    public function setWhitelistKey(?string $whitelistKey): void
    {
        $this->whitelistKey = $whitelistKey;
    }
}
