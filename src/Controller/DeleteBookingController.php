<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class DeleteBookingController extends AbstractController
{
    public function __invoke(string $data): string
    {
        die('F5');
    }
}
