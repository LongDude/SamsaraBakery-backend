<?php

namespace App\DataFixtures;

use App\Entity\Suppliers;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SuppliersFromCsvFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $csvFile = __DIR__ . '/Data/suppliers.csv'; // путь к вашему CSV

        if (!file_exists($csvFile)) {
            throw new \Exception("Файл $csvFile не найден!");
        }

        $handle = fopen($csvFile, 'r');
        if ($handle === false) {
            throw new \Exception("Не удалось открыть файл $csvFile");
        }

        $header = fgetcsv($handle, 1000, ';'); // читаем заголовки

        $rowId = 0;
        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            $row = array_combine($header, $data);

            $rowId++;
            $supplier = new Suppliers();
            $supplier->setFirmname($row['firmname']);
            $supplier->setAddress($row['address']);
            $supplier->setContactNumber($row['contact_number']);

            $manager->persist($supplier);
            $this->addReference('supplier_' . $rowId, $supplier);
        }

        fclose($handle);
        $manager->flush();
    }
}
