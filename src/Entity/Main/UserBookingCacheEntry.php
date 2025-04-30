<?php

namespace App\Entity\Main;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\Repository\UserBookingCacheEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(operations: [
    new Get(
        controller: NotFoundAction::class,
        openapiContext: ['description' => 'unsupported action', 'summary' => 'unsupported action'],
        output: false,
        read: false
    ),
    new GetCollection(
        uriTemplate: '/user-booking-cache-entries',
        openapiContext: [
            'description' => 'Retrieves user bookings entry from the cache table.',
            'summary' => 'Retrieves user bookings entry from the cache table.',
            'operationId' => 'get-v1-bookings-cache-entry',
            'parameters' => [
                [
                    'schema' => [
                        'type' => 'string',
                        'format' => 'string',
                    ],
                    'name' => 'resources',
                    'in' => 'query',
                    'description' => 'Resource of the booking, (email address)',
                ],
                [
                    'schema' => [
                        'type' => 'string',
                        'format' => 'string',
                    ],
                    'name' => 'uid',
                    'in' => 'query',
                    'description' => 'ID of the user to retrieve bookings for',
                ],
                [
                    'schema' => [
                        'type' => 'string',
                        'format' => 'string',
                        'example' => 'ACCEPTED',
                    ],
                    'name' => 'status',
                    'in' => 'query',
                    'description' => 'Status of the booking i.e. ACCEPTED or AWAITING_APPROVAL',
                ],
            ],

            'responses' => [
                '200' => [
                    'description' => 'OK',
                    'content' => [
                        'application/ld+json' => [
                            'examples' => [
                                'example1' => [
                                    'value' => [
                                        'exchangeId' => 'value1',
                                        'status' => 'value2',
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
        filters: ['user_booking_cache_entry.search_filter', 'user_booking_cache_entry.order_filter', 'user_booking_cache_entry.date_filter'],
    ),
],
    normalizationContext: [
        'groups' => ['userBookingCacheEntry'],
    ]
)]
#[ORM\Entity(repositoryClass: UserBookingCacheEntryRepository::class)]
class UserBookingCacheEntry
{
    #[Groups('userBookingCacheEntry')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups('userBookingCacheEntry')]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    // TODO: Remove Groups. ExchangeId should not be used in the frontend.
    #[Groups('userBookingCacheEntry')]
    #[ORM\Column(length: 255)]
    private ?string $exchangeId = null;

    #[ORM\Column(length: 255)]
    private ?string $uid = null;

    #[Groups('userBookingCacheEntry')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $start = null;

    #[Groups('userBookingCacheEntry')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $end = null;

    #[Groups('userBookingCacheEntry')]
    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[Groups('userBookingCacheEntry')]
    #[ORM\Column(length: 255)]
    private ?string $resourceMail = null;

    #[Groups('userBookingCacheEntry')]
    #[ORM\Column(length: 255)]
    private ?string $resourceDisplayName = null;

    #[Groups('userBookingCacheEntry')]
    #[ORM\Column(length: 255)]
    private ?string $iCalUId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getExchangeId(): ?string
    {
        return $this->exchangeId;
    }

    public function setExchangeId(string $exchangeId): static
    {
        $this->exchangeId = $exchangeId;

        return $this;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): static
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): static
    {
        $this->end = $end;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getResourceMail(): ?string
    {
        return $this->resourceMail;
    }

    public function setResourceMail(string $resourceMail): static
    {
        $this->resourceMail = $resourceMail;

        return $this;
    }

    public function getResourceDisplayName(): ?string
    {
        return $this->resourceDisplayName;
    }

    public function setResourceDisplayName(?string $resourceDisplayName): static
    {
        $this->resourceDisplayName = $resourceDisplayName;

        return $this;
    }

    public function getICalUId(): ?string
    {
        return $this->iCalUId;
    }

    public function setICalUId(?string $iCalUId): static
    {
        $this->iCalUId = $iCalUId;

        return $this;
    }
}
