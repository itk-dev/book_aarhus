<?php

namespace App\Service;

use App\Entity\Main\ApiKeyUser;
use App\Repository\Main\ApiKeyUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiKeyUserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ApiKeyUserRepository $apiKeyUserRepository
    ) {
    }

    /**
     * @throws Exception
     */
    public function createApiKey($name, $webformApiKey = null): ApiKeyUser
    {
        $apikey = hash('sha512', random_bytes(256));

        $apiKeyUser = new ApiKeyUser();
        $apiKeyUser->setApiKey($apikey);
        $apiKeyUser->setName($name);
        $apiKeyUser->setWebformApiKey($webformApiKey);

        $this->entityManager->persist($apiKeyUser);
        $this->entityManager->flush();

        return $apiKeyUser;
    }

    public function removeApiKey($id): void
    {
        $apiKeyUser = $this->apiKeyUserRepository->find($id);

        if (!$apiKeyUser) {
            throw new NotFoundHttpException();
        }

        $this->entityManager->remove($apiKeyUser);
        $this->entityManager->flush();
    }
}
