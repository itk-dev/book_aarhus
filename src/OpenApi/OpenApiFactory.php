<?php

namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;

class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private readonly OpenApiFactoryInterface $decorated
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        // Remove sub-resource with these paths.
        $excludes = [
            'bookings/{id}',
            'busy-intervals/{id}',
            'locations/{name}',
        ];

        $paths = $openApi->getPaths()->getPaths();

        $filteredPaths = new Paths();
        foreach ($paths as $path => $pathItem) {
            $split = explode('v1/', $path);

            if (2 == count($split) && in_array($split[1], $excludes)) {
                continue;
            }

            $filteredPaths->addPath($path, $pathItem);
        }

        return $openApi->withPaths($filteredPaths);
    }
}
