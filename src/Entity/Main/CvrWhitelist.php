<?php

namespace App\Entity\Main;

use App\Entity\Trait\IdTrait;
use App\Entity\Trait\SourceIdTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CvrWhitelist
{
    use IdTrait;
    use SourceIdTrait;

    #[ORM\ManyToOne(targetEntity: Resource::class, inversedBy: 'cvrWhitelists')]
    private Resource $resource;

    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private int $cvr;

    public function getCvr(): int
    {
        return $this->cvr;
    }

    public function setCvr(int $cvr): void
    {
        $this->cvr = $cvr;
    }

    public function getResource(): Resource
    {
        return $this->resource;
    }

    public function setResource(Resource $resource): void
    {
        $this->resource = $resource;
    }
}
