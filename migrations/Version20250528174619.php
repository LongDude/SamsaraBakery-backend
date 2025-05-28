<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528174619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE assortiment ALTER quantity TYPE INT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE assortiment ALTER quantity SET DEFAULT 0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE assortiment ALTER price SET DEFAULT '0'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE assortiment ALTER daily_delivery SET DEFAULT 0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deliveries ALTER quantity TYPE INT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deliveries ALTER quantity SET DEFAULT 0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deliveries ALTER price SET DEFAULT '0'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredients ALTER quantity TYPE INT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders ALTER price SET DEFAULT '0'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders ALTER quantity SET DEFAULT 0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE production_plan ALTER quantity SET DEFAULT 0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products ALTER production_cost SET DEFAULT '0'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products ALTER quantity_storaged SET DEFAULT 0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_movement ALTER realised_price SET DEFAULT '0'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_movement ALTER realised_count SET DEFAULT 0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_movement ALTER recieved_cost SET DEFAULT '0'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_movement ALTER recieved_count SET DEFAULT 0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_recipies ALTER quantity TYPE INT
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deliveries ALTER quantity TYPE DOUBLE PRECISION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deliveries ALTER quantity DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deliveries ALTER price DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE assortiment ALTER quantity TYPE DOUBLE PRECISION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE assortiment ALTER quantity DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE assortiment ALTER price DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE assortiment ALTER daily_delivery DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products ALTER production_cost DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products ALTER quantity_storaged DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders ALTER price DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders ALTER quantity DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_recipies ALTER quantity TYPE DOUBLE PRECISION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredients ALTER quantity TYPE DOUBLE PRECISION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE production_plan ALTER quantity DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_movement ALTER realised_price DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_movement ALTER realised_count DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_movement ALTER recieved_cost DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_movement ALTER recieved_count DROP DEFAULT
        SQL);
    }
}
