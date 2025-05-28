<?php

namespace App\DataFixtures;

use App\Entity\Partners;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PartnerRepresentatives extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $csvFile = __DIR__ . '/Data/partnerRepresentatives.csv'; // путь к вашему CSV

        if (!file_exists($csvFile)) {
            throw new \Exception("Файл $csvFile не найден!");
        }

        $handle = fopen($csvFile, 'r');
        if ($handle === false) {
            throw new \Exception("Не удалось открыть файл $csvFile");
        }

        $header = fgetcsv($handle, 1000, ','); // читаем заголовки

        $partnerRepository = $manager->getRepository(Partners::class);
        $userRepository = $manager->getRepository(User::class);
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $row = array_combine($header, $data);

            $partner = $this->getReference('partner_' . $row['partner_id'], Partners::class);
            $user = $this->getReference('user_' . $row['user_id'], User::class);

            $partner->addRepresentative($user);
            $manager->persist($partner);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PartnersFromCsvFixture::class,
            UsersFromCsvFixture::class,
        ];
    }
}
