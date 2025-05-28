<?php

namespace App\DataFixtures;

use App\Entity\Partners;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PartnersFromCsvFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $csvFile = __DIR__ . '/Data/partners.csv'; // путь к вашему CSV

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
            $partner = new Partners();
            $partner->setFirmname($row['firmname']);
            $partner->setAddress($row['address']);
            $partner->setContactNumber($row['contact_number']);
            $manager->persist($partner);
            $this->addReference('partner_' . $rowId, $partner);
        }

        fclose($handle);
        $manager->flush();
    }
}
