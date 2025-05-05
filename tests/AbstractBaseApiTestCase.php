<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Main\ApiKeyUser;
use App\Security\ApiKeyAuthenticator;
use PHPUnit\Framework\Constraint\Callback;

abstract class AbstractBaseApiTestCase extends ApiTestCase
{
    public const API_KEY = '1111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111';

    public static function setUpBeforeClass(): void
    {
        static::bootKernel();

        // TODO: Make sure the database is cleared.
    }

    protected function setUp(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        // Make sure test user exists.
        $testUser = $entityManager->getRepository(ApiKeyUser::class)->findBy(['name' => 'test']);
        if (!$testUser) {
            $apiKeyUser = new ApiKeyUser();
            $apiKeyUser->setName('test');
            $apiKeyUser->setApiKey(self::API_KEY);
            $apiKeyUser->setWebformApiKey(self::API_KEY);
            $entityManager->persist($apiKeyUser);
            $entityManager->flush();
        }
    }

    /**
     * Get an authenticated client.
     *
     * @return Client
     */
    protected function getAuthenticatedClient(): Client
    {
        return static::createClient([], ['headers' => [
            ApiKeyAuthenticator::AUTH_HEADER => ApiKeyAuthenticator::AUTH_HEADER_PREFIX.self::API_KEY,
            'Content-Type' => 'application/ld+json',
        ]]);
    }

    /**
     * Creates a callback used in replacement of withConsecutive().
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/4026.
     *
     * @param array $params
     *
     * @return Callback
     */
    protected function createCallback(array $params): Callback
    {
        return self::callback(function (mixed $message) use ($params) {
            static $invocationCount = 0;
            ++$invocationCount;

            return $message === $params[$invocationCount - 1];
        });
    }
}
