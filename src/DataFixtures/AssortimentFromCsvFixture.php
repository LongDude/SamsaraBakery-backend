<?php

namespace App\DataFixtures;

use App\Entity\Affiliates;
use App\Entity\Assortiment;
use App\Entity\Products;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AssortimentFromCsvFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $csvFile = __DIR__ . '/Data/assortiment.csv'; // путь к вашему CSV

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

            $assortiment = new Assortiment();
            $assortiment->setProduct($this->getReference('product_' . $row['product_id'], Products::class));
            $assortiment->setAffiliate($this->getReference('affiliate_' . $row['affiliate_id'], Affiliates::class));
            $assortiment->setQuantity($row['quantity']);
            $assortiment->setPrice($row['price']);
            $assortiment->setDailyDelivery($row['daily_delivery']);

            $manager->persist($assortiment);
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
