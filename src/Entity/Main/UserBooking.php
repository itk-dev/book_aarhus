<?php

namespace App\Entity\Main;

use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class UserBooking
{
    /**
     * @Groups({"userBooking"})
     */
    #[ApiProperty(identifier: true)]
    public string $id;

    /**
     * @Groups({"userBooking"})
     */
    public string $hitId;

    public string $iCalUId;

    /**
     * @Groups({"userBooking"})
     */
    public string $subject;

    /**
     * @Groups({"userBooking"})
     */
    public string $displayName;

    public string $body;

    /**
     * @Groups({"userBooking"})
     */
    public string $status;

    /**
     * @Groups({"userBooking"})
     */
    public ?\DateTimeInterface $start;

    /**
     * @Groups({"userBooking"})
     */
    public ?\DateTimeInterface $end;

    public string $bookingUniqueId;
}
