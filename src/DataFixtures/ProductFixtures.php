<?php

namespace App\DataFixtures;

use App\Factory\ProductsFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        ProductsFactory::createMany(100);
        $manager->flush();
    }
}
