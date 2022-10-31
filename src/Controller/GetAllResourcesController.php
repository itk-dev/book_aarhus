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
class GetAllResourcesController extends AbstractController
{
    public function __construct(
        private readonly AAKResourceRepository $aakResourceRepository,
        private readonly CacheInterface $resourceCache,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(Request $request): Response
    {
        $userPermissionHeader = $request->headers->get('Authorization-UserPermission');
        $whitelistKey = $request->query->get('whitelistKey');

        $userPermission = null;

        if (in_array($userPermissionHeader, ['citizen', 'businessPartner'])) {
            $userPermission = $userPermissionHeader;
        }

        $serializedResources = $this->resourceCache->get("resources-$userPermission", function (CacheItemInterface $cacheItem) use ($userPermission) {
            $cacheItem->expiresAfter(60 * 30); // 30 minutes.

            $info = $this->aakResourceRepository->getAllByPermission($userPermission);

            return $this->serializer->serialize($info, 'json', ['groups' => 'minimum']);
        });

        if (null !== $whitelistKey) {
            $info = $this->aakResourceRepository->getOnlyWhitelisted($userPermission, $whitelistKey);
            $serializedWhitelistedResources = $this->serializer->serialize($info, 'json', ['groups' => 'minimum']);
        }

        $resources = json_decode($serializedResources);

        if (isset($serializedWhitelistedResources)) {
            $resources = array_merge($resources, json_decode($serializedWhitelistedResources));
        }

        return new JsonResponse($resources, 200);
    }
}
