<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;

class BusyInterval
{
    #[ApiProperty(identifier: true)]
    public string $id;

    public string $resource;

    public ?\DateTimeInterface $dateFrom;

    public ?\DateTimeInterface $dateTo;
}
