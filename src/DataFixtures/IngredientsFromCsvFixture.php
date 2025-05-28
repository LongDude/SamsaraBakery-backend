<?php

namespace App\DataFixtures;

use App\Entity\Ingredients;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class IngredientsFromCsvFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $csvFile = __DIR__ . '/Data/ingredients.csv'; // путь к вашему CSV

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
            $ingredient = new Ingredients();
            $ingredient->setName($row['name']);
            $ingredient->setQuantity((float)$row['quantity']);

            $manager->persist($ingredient);
            $this->addReference('ingredient_' . $rowId, $ingredient);
        }

        fclose($handle);
        $manager->flush();
    }
}
