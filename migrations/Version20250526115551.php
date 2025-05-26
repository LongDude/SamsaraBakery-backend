<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250526115551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE assortiment ADD affiliate_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE assortiment ADD CONSTRAINT FK_7366C5789F12C49A FOREIGN KEY (affiliate_id) REFERENCES affiliates (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7366C5789F12C49A ON assortiment (affiliate_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE assortiment DROP CONSTRAINT FK_7366C5789F12C49A
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_7366C5789F12C49A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE assortiment DROP affiliate_id
        SQL);
    }
}
