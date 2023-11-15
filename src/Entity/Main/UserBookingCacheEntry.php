<?php

namespace App\Entity\Main;

use App\Repository\Main\UserBookingCacheEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserBookingCacheEntryRepository::class)]
class UserBookingCacheEntry
{
    #[Groups("userBookingCacheEntry")]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups("userBookingCacheEntry")]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    // TODO: Remove Groups. ExchangeId should not be used in the frontend.
    #[Groups("userBookingCacheEntry")]
    #[ORM\Column(length: 255)]
    private ?string $exchangeId = null;

    #[ORM\Column(length: 255)]
    private ?string $uid = null;

    #[Groups("userBookingCacheEntry")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $start = null;

    #[Groups("userBookingCacheEntry")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $end = null;

    #[Groups("userBookingCacheEntry")]
    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[Groups("userBookingCacheEntry")]
    #[ORM\Column(length: 255)]
    private ?string $resourceMail = null;

    #[Groups("userBookingCacheEntry")]
    #[ORM\Column(length: 255)]
    private ?string $resourceDisplayName = null;

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
}
