<?php

namespace App\Entity\Main;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Location
{
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(type: Types::STRING, unique: true, nullable: false)]
    private string $sourceId;

    #[ORM\Column(type: Types::STRING)]
    private string $displayName;

    #[ORM\Column(type: Types::STRING)]
    private string $address;

    #[ORM\Column(type: Types::STRING)]
    private string $city;

    #[ORM\Column(type: Types::STRING)]
    private string $postalCode;

    #[ORM\Column(type: Types::STRING)]
    private string $geoCoordinates;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSourceId(): string
    {
        return $this->sourceId;
    }

    public function setSourceId(string $sourceId): void
    {
        $this->sourceId = $sourceId;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getGeoCoordinates(): string
    {
        return $this->geoCoordinates;
    }

    public function setGeoCoordinates(string $geoCoordinates): void
    {
        $this->geoCoordinates = $geoCoordinates;
    }
}
