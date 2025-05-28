<?php

namespace App\DataFixtures;

use App\Entity\Affiliates;
use App\Entity\Products;
use App\Entity\ProductsMovement;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductMovementFromCsvFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $csvFile = __DIR__ . '/Data/productMovement.csv'; // путь к вашему CSV
        if (!file_exists($csvFile)) {
            throw new \Exception("Файл $csvFile не найден!");
        }
        $handle = fopen($csvFile, 'r');
        if ($handle === false) {
            throw new \Exception("Не удалось открыть файл $csvFile");
        }

        $header = fgetcsv($handle, 1000, ','); // читаем заголовки
        $affiliateRepository = $manager->getRepository(Affiliates::class);
        $productRepository = $manager->getRepository(Products::class);
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $row = array_combine($header, $data);

            $productMovement = new ProductsMovement();
            $productMovement->setAffiliate($this->getReference('affiliate_' . $row['affiliate_id'], Affiliates::class));
            $productMovement->setProduct($this->getReference('product_' . $row['product_id'], Products::class));
            $productMovement->setRealisedPrice($row['realised_price']);
            $productMovement->setRealisedCount($row['realised_count']);
            $productMovement->setRecievedCost($row['recieved_cost']);
            $productMovement->setRecievedCount($row['recieved_count']);
            $productMovement->setDate(DateTime::createFromFormat('Y-m-d', $row['date']));

            $manager->persist($productMovement);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AffiliatesFromCsvFixture::class,
            ProductFromCsvFixture::class,
        ];
    }
}
