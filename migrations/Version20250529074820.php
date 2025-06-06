<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250529074820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE partners ALTER address TYPE VARCHAR(128)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE suppliers ALTER firmname TYPE VARCHAR(128)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE suppliers ALTER address TYPE VARCHAR(128)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER email TYPE VARCHAR(64)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER username TYPE VARCHAR(64)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE suppliers ALTER firmname TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE suppliers ALTER address TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER email TYPE VARCHAR(180)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER username TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE partners ALTER address TYPE VARCHAR(255)
        SQL);
    }
}
