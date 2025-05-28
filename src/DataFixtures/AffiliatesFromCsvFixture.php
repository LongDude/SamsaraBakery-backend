<?php

namespace App\DataFixtures;

use App\Entity\Affiliates;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AffiliatesFromCsvFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $csvFile = __DIR__ . '/Data/affiliates.csv'; // путь к вашему CSV

        if (!file_exists($csvFile)) {
            throw new \Exception("Файл $csvFile не найден!");
        }

        $handle = fopen($csvFile, 'r');
        if ($handle === false) {
            throw new \Exception("Не удалось открыть файл $csvFile");
        }

        $header = fgetcsv($handle, 1000, ';'); // читаем заголовки
        $userRepository = $manager->getRepository(User::class);
        
        $rowId = 0;
        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            $row = array_combine($header, $data);

            $rowId++;
            $affiliate = new Affiliates();
            $affiliate->setAddress($row['address']);
            $affiliate->setContactNumber($row['contact_number']);
            $affiliate->setManager($this->getReference('user_' . $row['manager_id'], User::class));

            $manager->persist($affiliate);
            $this->addReference('affiliate_' . $rowId, $affiliate);
        }
        
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsersFromCsvFixture::class,
        ];
    }
}
