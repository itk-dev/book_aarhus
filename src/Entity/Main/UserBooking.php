<?php

namespace App\Entity\Main;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\GetAllResourcesController;
use App\Controller\GetResourceByEmailController;
use App\State\UserBookingCollectionProvider;
use App\State\UserBookingPersister;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(

    operations: [

        new Get(
            uriTemplate: '/user-bookings/{id}',
            normalizationContext: [
                'groups' => ['userBooking'],
            ],
        ),
        new Delete(
            uriTemplate: '/user-bookings/{id}',
            normalizationContext: [
                'groups' => ['userBooking'],
            ],


        ),
        new Patch(
            uriTemplate: '/user-bookings/{id}',
            normalizationContext: [
                'groups' => ['userBooking'],
            ],
        ),
        new Post(
            uriTemplate: '/status-by-ids',
            controller: 'App\Controller\GetStatusByIdsController',
            openapiContext: [],
            shortName: 'Userbooking',
            input: 'App\Dto\UserBookingStatusInput',
        ),
        new GetCollection(
            uriTemplate: '/user-bookings',
            openapiContext: [
                'description' => 'Retrieves user bookings from a specific user',
                'summary' => 'Retrieves user bookings from a specific user',
                'operationId' => 'get-v1-bookings',
                'headers' => [],
                'parameters'=> [
                    [
                        'schema' => [
                            'type' => 'string',
                            'format' => 'string',
                            'example' => "1"
                        ],
                        'in' => 'query',
                        'required' => true,
                        'description' => "ID of the user to retrieve bookings for",
                        'name' => 'userId'
                    ],
    ],

                'response' =>[
                    '200' => [
                        'description' => 'OK',
                        'content' => [
                            'application/ld+json' => [
                                'examples' => [
                                    'example1' => [
                                        'value' => [

                                              "id" => "1",
                                              "subject" => "Horse species",
                                              "displayName" => "Pony",
                                              "status" => "away from keyboard",
                                              "bookingType" => "Rent",
                                              "expired" => false,
                                              "start" => "2024-01-16T08:02:25.084Z",
                                              "end" => "2024-01-16T08:02:25.084Z",
                                              "resourceMail" => "Horse@Pony.dk",
                                              "resourceName" => "Book about Hors species"
                                        ],
                                        'summary' => 'An example of a JSON-LD response',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],

],
            shortName: 'Userbooking',
            normalizationContext: [
                'groups' => ['userBooking'],
            ],


        )
    ],
    normalizationContext: [
        'groups' => ['userBooking'],
    ],
    provider: UserBookingCollectionProvider::class,
    processor: UserBookingPersister::class,
)]

class UserBooking
{
    /**
     * @Groups({"userBooking"})
     */
    #[ApiProperty(identifier: true)]
    public string $id;

    public string $iCalUId;

    /**
     * @Groups({"userBooking"})
     */
    public string $subject;

    /**
     * @Groups({"userBooking"})
     */
    public string $displayName;

    public string $body;

    /**
     * @Groups({"userBooking"})
     */
    public string $status;

    /**
     * @Groups({"userBooking"})
     */
    public string $bookingType;

    /**
     * @Groups({"userBooking"})
     */
    public bool $expired;

    /**
     * @Groups({"userBooking"})
     */
    public \DateTime $start;

    /**
     * @Groups({"userBooking"})
     */
    public \DateTime $end;

    /**
     * @Groups({"userBooking"})
     */
    public string $resourceMail;

    /**
     * @Groups({"userBooking"})
     */
    public string $resourceName;

    public bool $ownedByServiceAccount;

    public string $userEmail;

    public string $userName;
}
