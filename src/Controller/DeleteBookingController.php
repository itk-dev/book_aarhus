<?php

namespace App\Controller;

use App\Entity\Main\UserBooking;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class DeleteBookingController extends AbstractController
{
    public function __invoke(UserBooking $data): UserBooking
    {
        return $data;
    }
}
