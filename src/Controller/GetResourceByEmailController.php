<?php

namespace App\Controller;

use App\Repository\Resources\AAKResourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class GetResourceByEmailController extends AbstractController
{
    public function __construct(
        private readonly AAKResourceRepository $aakResourceRepository,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function __invoke(Request $request, string $resourceMail): Response
    {
        $resource = $this->aakResourceRepository->findOneByEmail($resourceMail);

        if (is_null($resource)) {
            throw new HttpException(404, 'Resource not found');
        }

        $data = $this->serializer->serialize($resource, 'json', ['groups' => 'resource']);

        return new Response($data, 200);
    }
}
