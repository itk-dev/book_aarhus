<?php

namespace App\Controller;

use App\Repository\Main\AAKResourceRepository;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        // If whitelistKey is set, we do no rely on cache.
        // @TODO: This should also be cacheable on a whitelistKey basis.
        if (null !== $whitelistKey) {
            $info = $this->aakResourceRepository->getAllByPermission(null, $whitelistKey);

            $value = $this->serializer->serialize($info, 'json', ['groups' => 'minimum']);

            return new Response($value, 200);
        }

        $userPermission = null;

        if (in_array($userPermissionHeader, ['citizen', 'businessPartner'])) {
            $userPermission = $userPermissionHeader;
        }

        $value = $this->resourceCache->get("resources-$userPermission", function (CacheItemInterface $cacheItem) use ($userPermission) {
            $cacheItem->expiresAfter(60 * 30); // 30 minutes.

            $info = $this->aakResourceRepository->getAllByPermission($userPermission);
            return $this->serializer->serialize($info, 'json', ['groups' => 'minimum']);
        });

        return new Response($value, 200);
    }
}
