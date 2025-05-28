<?php

namespace App\DataFixtures;

use App\Entity\ProductionPlan;
use App\Entity\Products;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductionPlanFromCsvFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $csvFile = __DIR__ . '/Data/productionPlan.csv'; // путь к вашему CSV

        if (!file_exists($csvFile)) {
            throw new \Exception("Файл $csvFile не найден!");
        }

        $handle = fopen($csvFile, 'r');
        if ($handle === false) {
            throw new \Exception("Не удалось открыть файл $csvFile");
        }

        $header = fgetcsv($handle, 1000, ','); // читаем заголовки

        $productRepository = $manager->getRepository(Products::class);

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $row = array_combine($header, $data);

            $productionPlan = new ProductionPlan();
            $productionPlan->setProduct($this->getReference('product_' . $row['product_id'], Products::class));
            $productionPlan->setQuantity($row['quantity']);
            $productionPlan->setDate(DateTime::createFromFormat('Y-m-d', $row['date']));

            $manager->persist($productionPlan);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProductFromCsvFixture::class,
        ];
    }
}
