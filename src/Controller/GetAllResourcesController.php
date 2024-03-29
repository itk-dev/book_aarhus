<?php

namespace App\Controller;

use App\Service\ResourceServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetAllResourcesController extends AbstractController
{
    public function __construct(
        private readonly ResourceServiceInterface $resourceService,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $userPermissionHeader = $request->headers->get('Authorization-UserPermission');
        $whitelistKey = $request->query->get('whitelistKey');

        $permission = null;

        if (in_array($userPermissionHeader, ['citizen', 'businessPartner'])) {
            $permission = $userPermissionHeader;
        }

        $resources = $this->resourceService->getAllResources($permission);

        if (null !== $whitelistKey && null !== $permission) {
            $whitelistedResources = $this->resourceService->getWhitelistedResources($permission, $whitelistKey);
            $resources = array_merge($resources, $whitelistedResources);
        }

        return new JsonResponse($resources, 200);
    }
}
