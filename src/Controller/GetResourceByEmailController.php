<?php

namespace App\Controller;

use App\Repository\Resources\AAKResourceRepository;
use App\Service\Metric;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class GetResourceByEmailController extends AbstractController
{
    public function __construct(
        private readonly AAKResourceRepository $aakResourceRepository,
        private readonly SerializerInterface $serializer,
        private readonly Metric $metric,
    ) {
    }

    public function __invoke(Request $request, string $resourceMail): Response
    {
        $this->metric->incFunctionTotal($this, __FUNCTION__, Metric::INVOKE);

        $resource = $this->aakResourceRepository->findOneByEmail($resourceMail);

        if (is_null($resource)) {
            $this->metric->incExceptionTotal(NotFoundHttpException::class);
            throw new NotFoundHttpException('Resource not found');
        }

        $data = $this->serializer->serialize($resource, 'json', ['groups' => 'resource']);

        $this->metric->incFunctionTotal($this, __FUNCTION__, Metric::COMPLETE);

        return new Response($data, 200);
    }
}
