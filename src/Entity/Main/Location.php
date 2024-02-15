<?php

namespace App\Entity\Main;

use ApiPlatform\Action\NotFoundAction;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\State\LocationCollectionProvider;

#[ApiResource(operations: [
    new Get(
        controller: NotFoundAction::class,
        openapiContext: ['description' => 'unsupported action'],
        output: false,
        read: false
    ),
    new GetCollection(
        uriTemplate: '/locations',
        openapiContext: [
            'description' => 'Retrieves locations.',
            'summary' => 'Retrieves locations.',
            'operationId' => 'get-v1-locations',
            'parameters' => [],

            'responses' => [
                '200' => [
                    'description' => 'OK',
                    'content' => [
                        'application/ld+json' => [
                            'examples' => [
                                'example1' => [
                                    'value' => [
                                        '@id' => '/v1/locations/LOCATION1',
                                        '@type' => 'Location',
                                        'name' => 'Example Location',
                                    ],
                                    'summary' => 'An example of a JSON-LD response',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'headers' => [],
        ],
        provider: LocationCollectionProvider::class,
    ),
])]
class Location
{
    #[ApiProperty(identifier: true)]
    public string $name;
}
