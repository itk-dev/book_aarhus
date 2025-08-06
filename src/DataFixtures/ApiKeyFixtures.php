<?php

namespace App\DataFixtures;

use App\Entity\Main\ApiKeyUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ApiKeyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $apiKey = new ApiKeyUser();
        $apiKey->setApiKey('ef2bf626d331e7e9543b5877a3ba7c37913b0ee7a1b975d033f653e1f0d52d215451f976c0d6006518fbc0fcbda12280b6b4e7a90535138fb79bcc3686e2653e');
        $apiKey->setWebformApiKey('some_webform_api_key');
        $apiKey->setName('fixtureApiKey');
        $manager->persist($apiKey);
        $manager->flush();
    }
}
