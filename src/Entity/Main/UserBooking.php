<?php

namespace App\Entity\Main;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserBookingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserBookingRepository::class)]
#[ApiResource]
class UserBooking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
