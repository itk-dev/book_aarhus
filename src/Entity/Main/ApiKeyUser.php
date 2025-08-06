<?php

namespace App\Entity\Main;

use App\Entity\Trait\IdTrait;
use App\Repository\ApiKeyUserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ApiKeyUserRepository::class)]
class ApiKeyUser implements UserInterface
{
    use IdTrait;

    private const ROLES = ['ROLE_USER'];

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Assert\Length(
        min: 80,
        max: 255,
        minMessage: 'Api key must be at least {{ limit }} characters long',
        maxMessage: 'Api key cannot be longer than {{ limit }} characters',
    )]
    private string $apiKey;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $webformApiKey = null;

    public function getRoles(): array
    {
        return self::ROLES;
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->getApiKey();
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getWebformApiKey(): ?string
    {
        return $this->webformApiKey;
    }

    public function setWebformApiKey(?string $webformApiKey): void
    {
        $this->webformApiKey = $webformApiKey;
    }
}
