<?php

namespace App\DataFixtures;

use App\Entity\Deliveries;
use App\Entity\Ingredients;
use App\Entity\Suppliers;
use App\Enum\OrderStatus;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DeliveriesFromCsvFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $csvFile = __DIR__ . '/Data/deliveries.csv'; // путь к вашему CSV

        if (!file_exists($csvFile)) {
            throw new \Exception("Файл $csvFile не найден!");
        }

        $handle = fopen($csvFile, 'r');
        if ($handle === false) {
            throw new \Exception("Не удалось открыть файл $csvFile");
        }

        $header = fgetcsv($handle, 1000, ','); // читаем заголовки

        $supplierRepository = $manager->getRepository(Suppliers::class);
        $ingredientRepository = $manager->getRepository(Ingredients::class);
         
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $row = array_combine($header, $data);

            $delivery = new Deliveries();
            $delivery->setSupplier($this->getReference('supplier_' . $row['supplier_id'], Suppliers::class));
            $delivery->setIngredient($this->getReference('ingredient_' . $row['ingredient_id'], Ingredients::class));
            $delivery->setQuantity($row['quantity']);
            $delivery->setPrice($row['price']);
            $delivery->setDate(DateTime::createFromFormat('Y-m-d', $row['date']));
            $delivery->setStatus(OrderStatus::from($row['status']));

            $manager->persist($delivery);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SuppliersFromCsvFixture::class,
            IngredientsFromCsvFixture::class,
        ];
    }
}
