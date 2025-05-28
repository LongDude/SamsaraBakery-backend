<?php

namespace App\DataFixtures;

use App\Entity\Orders;
use App\Entity\Partners;
use App\Entity\Products;
use App\Enum\OrderStatus;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrdersFromCsvFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $csvFile = __DIR__ . '/Data/orders.csv'; // путь к вашему CSV

        if (!file_exists($csvFile)) {
            throw new \Exception("Файл $csvFile не найден!");
        }

        $handle = fopen($csvFile, 'r');
        if ($handle === false) {
            throw new \Exception("Не удалось открыть файл $csvFile");
        }

        $header = fgetcsv($handle, 1000, ','); // читаем заголовки

        $partnerRepository = $manager->getRepository(Partners::class);
        $productRepository = $manager->getRepository(Products::class);

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $row = array_combine($header, $data);

            $order = new Orders();
            $order->setRecieverPartner($this->getReference('partner_' . $row['reciever_partner_id'], Partners::class));
            $order->setProduct($this->getReference('product_' . $row['product_id'], Products::class));
            $order->setPrice($row['price']);
            $order->setQuantity($row['quantity']);
            $order->setStatus(OrderStatus::from($row['status']));
            $order->setDate(DateTime::createFromFormat('Y-m-d', $row['date']));

            $manager->persist($order);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PartnersFromCsvFixture::class,
            ProductFromCsvFixture::class,
        ];
    }
}
