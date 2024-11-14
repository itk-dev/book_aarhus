<?php

namespace App\Entity\Main;

use ApiPlatform\Action\NotFoundAction;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\State\BusyIntervalCollectionProvider;

#[ApiResource(
    operations: [
        new Get(
            controller: NotFoundAction::class,
            openapiContext: ['description' => 'unsupported action'],
            output: false,
            read: false
        ),
        new GetCollection(
            uriTemplate: '/busy-intervals',
            openapiContext: [
                'operationId' => 'get-v1-busy-intervals',
                'description' => 'Retrieves busy intervals.',
                'summary' => 'Retrieves busy intervals.',

                'responses' => [
                    '200' => [
                        'description' => 'OK',
                        'content' => [
                            'application/ld+json' => [
                                'examples' => [
                                    'example1' => [
                                        'value' => [
                                            '@context' => 'https://www.w3.org/ns/hydra/context.jsonld',
                                            '@id' => '/bu/1',
                                            'name' => 'Example BusyInterval',
                                            'resource' => 'test@bookaarhus.local.itkdev.dk',
                                            'dateStart' => '2022-05-30T17:32:28Z',
                                            'dateEnd' => '2022-06-22T17:32:28Z',
                                        ],
                                        'summary' => 'An example of a JSON-LD response',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'headers' => [],

                'parameters' => [
                    [
                        'schema' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'string',
                                'example' => 'test@bookaarhus.local.itkdev.dk',
                            ],
                        ],
                        'name' => 'resources',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'Array of resources to request busy intervals for.',
                    ],
                    [
                        'schema' => [
                            'type' => 'string',
                            'format' => 'date-time',
                            'example' => '2022-05-30T17:32:28Z',
                        ],
                        'name' => 'dateStart',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'Start time for the search interval (DateTime. Expected format: "2022-05-30T17:32:28Z")',
                    ],
                    [
                        'schema' => [
                            'type' => 'string',
                            'format' => 'date-time',
                            'example' => '2022-05-30T17:32:28Z',
                        ],
                        'name' => 'dateEnd',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'End time for the search interval (DateTime. Expected format: "2022-05-30T17:32:28Z")',
                    ],
                ],
            ],
            shortName: 'BusyInterval',
            provider: BusyIntervalCollectionProvider::class,
        ),
    ])]
class BusyInterval
{
    #[ApiProperty(identifier: true)]
    public string $id;

    public string $resource;

    public ?\DateTimeInterface $startTime;

    public ?\DateTimeInterface $endTime;
}
