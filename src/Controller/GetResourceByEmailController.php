<?php

namespace App\Controller;

use App\Repository\ResourceRepository;
use App\Service\MetricsHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class GetResourceByEmailController extends AbstractController
{
    public function __construct(
        private readonly ResourceRepository $aakResourceRepository,
        private readonly SerializerInterface $serializer,
        private readonly MetricsHelper $metricsHelper,
    ) {
    }

    public function __invoke(string $resourceMail): Response
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $resource = $this->aakResourceRepository->findOneByEmail($resourceMail);

        if (is_null($resource)) {
            $this->metricsHelper->incExceptionTotal(NotFoundHttpException::class);
            throw new NotFoundHttpException('Resource not found');
        }

        $data = $this->serializer->serialize($resource, 'json', ['groups' => 'resource']);

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return new Response($data, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
    }
}
