<?php

namespace App\Entity\Main;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\UserBookingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: UserBookingRepository::class)]
#[ApiResource(operations:
    new Post(
        uriTemplate: '/status-by-ids',
        controller: 'App\Controller\GetStatusByIdsController',
        openapiContext: [],
        normalizationContext: [
            'groups' => ['userBooking'],
        ],
        input: 'App\Dto\UserBookingStatusInput',
    ),
)]


class UserBooking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[ApiProperty(identifier: true)]
    #[Groups(['userBooking'])]
    private ?int $id = null;

//    public function getId(): ?int
//    {
//        return $this->id;
//    }
}
