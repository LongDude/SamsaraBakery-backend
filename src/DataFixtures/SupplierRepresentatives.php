<?php

namespace App\DataFixtures;

use App\Entity\Suppliers;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SupplierRepresentatives extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $csvFile = __DIR__ . '/Data/supplierRepresentatives.csv'; // путь к вашему CSV

        if (!file_exists($csvFile)) {
            throw new \Exception("Файл $csvFile не найден!");
        }

        $handle = fopen($csvFile, 'r');
        if ($handle === false) {
            throw new \Exception("Не удалось открыть файл $csvFile");
        }

        $header = fgetcsv($handle, 1000, ','); // читаем заголовки

        $supplierRepository = $manager->getRepository(Suppliers::class);
        $userRepository = $manager->getRepository(User::class);
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $row = array_combine($header, $data);

            $supplier = $this->getReference('supplier_' . $row['supplier_id'], Suppliers::class);
            $user = $this->getReference('user_' . $row['user_id'], User::class);

            $supplier->addRepresentative($user);
            $manager->persist($supplier);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SuppliersFromCsvFixture::class,
            UsersFromCsvFixture::class,
        ];
    }
}
