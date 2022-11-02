<?php

namespace App\Entity\Main;

use ApiPlatform\Core\Annotation\ApiProperty;

class BusyInterval
{
    #[ApiProperty(identifier: true)]
    public string $id;

    public string $resource;

    public ?\DateTimeInterface $startTime;

    public ?\DateTimeInterface $endTime;
}
