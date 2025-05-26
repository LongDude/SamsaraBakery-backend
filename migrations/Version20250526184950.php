<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250526184950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE ingredients_products (ingredients_id INT NOT NULL, products_id INT NOT NULL, PRIMARY KEY(ingredients_id, products_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C26954133EC4DCE ON ingredients_products (ingredients_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C26954136C8A81A9 ON ingredients_products (products_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredients_products ADD CONSTRAINT FK_C26954133EC4DCE FOREIGN KEY (ingredients_id) REFERENCES ingredients (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredients_products ADD CONSTRAINT FK_C26954136C8A81A9 FOREIGN KEY (products_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
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
            ALTER TABLE ingredients_products DROP CONSTRAINT FK_C26954133EC4DCE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredients_products DROP CONSTRAINT FK_C26954136C8A81A9
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ingredients_products
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
