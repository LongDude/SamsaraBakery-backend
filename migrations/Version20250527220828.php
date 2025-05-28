<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527220828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredients_products DROP CONSTRAINT fk_c26954133ec4dce
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredients_products DROP CONSTRAINT fk_c26954136c8a81a9
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ingredients_products
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_recipies ADD quantity DOUBLE PRECISION DEFAULT '0' NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ingredients_products (ingredients_id INT NOT NULL, products_id INT NOT NULL, PRIMARY KEY(ingredients_id, products_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_c26954136c8a81a9 ON ingredients_products (products_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_c26954133ec4dce ON ingredients_products (ingredients_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredients_products ADD CONSTRAINT fk_c26954133ec4dce FOREIGN KEY (ingredients_id) REFERENCES ingredients (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredients_products ADD CONSTRAINT fk_c26954136c8a81a9 FOREIGN KEY (products_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_recipies DROP quantity
        SQL);
    }
}
