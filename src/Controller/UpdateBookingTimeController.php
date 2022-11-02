<?php

namespace App\Controller;

use App\Repository\Main\AAKResourceRepository;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsController]
class UpdateBookingTimeController extends AbstractController
{
    public function __construct(
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(Request $request): Response
    {

        return new JsonResponse([], 201);
    }
}
