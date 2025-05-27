<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527193902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_ADDRESS ON affiliates (address)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_NAME ON products (name)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE suppliers ADD contact_number VARCHAR(20) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE suppliers DROP contact_number
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_ADDRESS
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_NAME
        SQL);
    }
}
