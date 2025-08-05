<?php

namespace App\Entity\Main;

use App\Entity\Trait\IdTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Location
{
    use IdTrait;

    #[ORM\Column(type: Types::STRING, unique: true)]
    private string $location;

    /**
     * @var Collection<int, resource>
     */
    #[ORM\OneToMany(mappedBy: 'location', targetEntity: Resource::class)]
    private Collection $resources;

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

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
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

    public function __toString(): string
    {
        return $this->location;
    }
}
