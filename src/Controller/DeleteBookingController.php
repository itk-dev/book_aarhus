<?php

namespace App\Controller;

use App\Entity\Main\UserBooking;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



final class DeleteBookingController extends AbstractController
{
    function __invoke(UserBooking $data): UserBooking
    {
        return $data;
    }
}
