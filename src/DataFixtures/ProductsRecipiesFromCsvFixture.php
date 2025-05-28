<?php

namespace App\DataFixtures;

use App\Entity\Ingredients;
use App\Entity\Products;
use App\Entity\ProductsRecipies;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductsRecipiesFromCsvFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $csvFile = __DIR__ . '/Data/productRecipies.csv'; // путь к вашему CSV

        if (!file_exists($csvFile)) {
            throw new \Exception("Файл $csvFile не найден!");
        }

        $handle = fopen($csvFile, 'r');
        if ($handle === false) {
            throw new \Exception("Не удалось открыть файл $csvFile");
        }

        $header = fgetcsv($handle, 1000, ','); // читаем заголовки

        $productRepository = $manager->getRepository(Products::class);
        $ingredientRepository = $manager->getRepository(Ingredients::class);

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $row = array_combine($header, $data);

            $productRecipie = new ProductsRecipies();
            $productRecipie->setProductId($this->getReference('product_' . $row['product_id'], Products::class));
            $productRecipie->setIngredientId($this->getReference('ingredient_' . $row['ingredient_id'], Ingredients::class));
            $productRecipie->setQuantity($row['quantity']);

            $manager->persist($productRecipie);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProductFromCsvFixture::class,
            IngredientsFromCsvFixture::class,
        ];
    }
}
