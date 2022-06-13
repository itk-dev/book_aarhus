<?php

namespace App\Service;

use App\Entity\ApiKeyUser;
use Doctrine\ORM\EntityManagerInterface;

class ApiKeyUserService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @throws \Exception
     */
    public function createApiKey($name): ApiKeyUser
    {
        $apikey = hash('sha512', random_bytes(256));

        $apiKeyUser = new ApiKeyUser();
        $apiKeyUser->setApiKey($apikey);
        $apiKeyUser->setName($name);

        $this->entityManager->persist($apiKeyUser);
        $this->entityManager->flush();

        return $apiKeyUser;
    }
}
