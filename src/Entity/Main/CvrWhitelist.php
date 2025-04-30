<?php

namespace App\Entity\Main;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CvrWhitelist
{
    /**
     * @var int
     */
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    /**
     * @var int
     */
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private int $resourceId;

    /**
     * @var int
     */
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private int $cvr;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $updateTimestamp;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getResourceId(): int
    {
        return $this->resourceId;
    }

    public function setResourceId(int $resourceId): void
    {
        $this->resourceId = $resourceId;
    }

    public function getCvr(): int
    {
        return $this->cvr;
    }

    public function setCvr(int $cvr): void
    {
        $this->cvr = $cvr;
    }

    public function getUpdateTimestamp(): \DateTime
    {
        return $this->updateTimestamp;
    }

    public function setUpdateTimestamp(\DateTime $updateTimestamp): void
    {
        $this->updateTimestamp = $updateTimestamp;
    }
}
