<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527220507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE products_recipies (id SERIAL NOT NULL, product_id_id INT NOT NULL, ingredient_id_id INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A025BDEADE18E50B ON products_recipies (product_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A025BDEA6676F996 ON products_recipies (ingredient_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_recipies ADD CONSTRAINT FK_A025BDEADE18E50B FOREIGN KEY (product_id_id) REFERENCES products (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_recipies ADD CONSTRAINT FK_A025BDEA6676F996 FOREIGN KEY (ingredient_id_id) REFERENCES ingredients (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_recipies DROP CONSTRAINT FK_A025BDEADE18E50B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_recipies DROP CONSTRAINT FK_A025BDEA6676F996
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE products_recipies
        SQL);
    }
}
