<?php

namespace App\Entity;

use App\Repository\ApiKeyUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ApiKeyUserRepository::class)]
class ApiKeyUser implements UserInterface
{
    private const ROLES = ['ROLE_USER'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\Length(
        min: 80,
        max: 255,
        minMessage: 'Api key must be at least {{ limit }} characters long',
        maxMessage: 'Api key cannot be longer than {{ limit }} characters',
    )]
    private string $apiKey;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoles(): array
    {
        return self::ROLES;
    }

    public function eraseCredentials()
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
}
