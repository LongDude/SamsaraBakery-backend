<?php

namespace App\DataFixtures;

use App\Entity\Products;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFromCsvFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $csvFile = __DIR__ . '/Data/products.csv'; // путь к вашему CSV

        if (!file_exists($csvFile)) {
            throw new \Exception("Файл $csvFile не найден!");
        }

        $handle = fopen($csvFile, 'r');
        if ($handle === false) {
            throw new \Exception("Не удалось открыть файл $csvFile");
        }

        $header = fgetcsv($handle, 1000, ','); // читаем заголовки

        $rowId = 0;
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $row = array_combine($header, $data);

            $rowId++;
            $product = new Products();
            $product->setName($row['name']);
            $product->setProductionCost((float)$row['production_cost']);
            $product->setQuantityStoraged((int)$row['quantity_storaged']);
            // Добавьте другие поля, если нужно

            $manager->persist($product);
            $this->addReference('product_' . $rowId, $product);
        }

        fclose($handle);
        $manager->flush();
    }
}
