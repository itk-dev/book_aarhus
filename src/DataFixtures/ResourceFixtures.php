<?php

namespace App\DataFixtures;

use App\Entity\Resource;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ResourceFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $resource1 = new Resource();
        $resource1->setTitle('dokk1-lokale-test1@aarhus.dk')
        ->setCapacity(10)
        ->setName('Dokk1 testlokale 1');

        $resource2 = new Resource();
        $resource2->setTitle('dokk1-lokale-test2@aarhus.dk')
        ->setCapacity(40)
        ->setName('Dokk1 testlokale 2');

        $resource3 = new Resource();
        $resource3->setTitle('dokk1-lokale-test3@aarhus.dk')
        ->setCapacity(20)
        ->setName('Dokk1 testlokale 3');

        $resource4 = new Resource();
        $resource4->setTitle('dokk1-lokale-test4@aarhus.dk')
        ->setCapacity(15)
        ->setName('Dokk1 testlokale 4');

        $manager->persist($resource1);
        $manager->persist($resource2);
        $manager->persist($resource3);
        $manager->persist($resource4);

        $manager->flush();
    }
}
