<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UsersFromCsvFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $csvFile = __DIR__ . '/Data/users.csv'; // путь к вашему CSV

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
            $user = new User();
            $user->setEmail($row['email']);
            $user->setRoles([$row['roles']]);
            $user->setPassword($row['password']);
            $user->setPhone($row['phone']);
            $user->setUsername($row['username']);

            $manager->persist($user);
            $this->addReference('user_' . $rowId, $user);
        }

        fclose($handle);
        $manager->flush();
    }
}
