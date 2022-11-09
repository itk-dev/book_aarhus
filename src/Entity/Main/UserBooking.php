<?php

namespace App\Entity\Main;

use ApiPlatform\Core\Annotation\ApiProperty;
use DateTime;
use Symfony\Component\Serializer\Annotation\Groups;

class UserBooking
{
    /**
     * @Groups({"userBooking"})
     */
    #[ApiProperty(identifier: true)]
    public string $id;

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
    public string $bookingType;

    /**
     * @Groups({"userBooking"})
     */
    public bool $expired;

    /**
     * @Groups({"userBooking"})
     */
    public DateTime $start;

    /**
     * @Groups({"userBooking"})
     */
    public DateTime $end;

    /**
     * @Groups({"userBooking"})
     */
    public string $resourceMail;

    /**
     * @Groups({"userBooking"})
     */
    public string $resourceName;

    public bool $ownedByServiceAccount;
}
