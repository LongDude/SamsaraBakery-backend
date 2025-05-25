<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250525203523 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE affiliates (id SERIAL NOT NULL, manager_id INT DEFAULT NULL, address VARCHAR(128) NOT NULL, contact_number VARCHAR(20) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_108C6A8F783E3463 ON affiliates (manager_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE assortiment (id SERIAL NOT NULL, product_id INT NOT NULL, quantity DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION NOT NULL, daily_delivery INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7366C5784584665A ON assortiment (product_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE deliveries (id SERIAL NOT NULL, supplier_id INT NOT NULL, ingredient_id INT NOT NULL, date DATE NOT NULL, quantity DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6F0785682ADD6D8C ON deliveries (supplier_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6F078568933FE08C ON deliveries (ingredient_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ingredients (id SERIAL NOT NULL, quantity DOUBLE PRECISION NOT NULL, name VARCHAR(128) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE orders (id SERIAL NOT NULL, reciever_partner_id INT NOT NULL, product_id INT NOT NULL, price DOUBLE PRECISION NOT NULL, quantity INT NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E52FFDEE32E0841B ON orders (reciever_partner_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E52FFDEE4584665A ON orders (product_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE partners (id SERIAL NOT NULL, firmname VARCHAR(128) NOT NULL, address VARCHAR(255) NOT NULL, contact_number VARCHAR(20) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE partners_user (partners_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(partners_id, user_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B3E4CF7ABDE7F1C6 ON partners_user (partners_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B3E4CF7AA76ED395 ON partners_user (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE production_plan (id SERIAL NOT NULL, product_id INT NOT NULL, date DATE NOT NULL, quantity INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_64A10694584665A ON production_plan (product_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE products (id SERIAL NOT NULL, production_cost DOUBLE PRECISION NOT NULL, name VARCHAR(128) NOT NULL, quantity_storaged INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE products_movement (id SERIAL NOT NULL, affiliate_id INT NOT NULL, product_id INT NOT NULL, realised_price DOUBLE PRECISION NOT NULL, realised_count INT NOT NULL, recieved_cost DOUBLE PRECISION NOT NULL, recieved_count INT NOT NULL, date DATE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E1DC85449F12C49A ON products_movement (affiliate_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E1DC85444584665A ON products_movement (product_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE suppliers (id SERIAL NOT NULL, firmname VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE suppliers_user (suppliers_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(suppliers_id, user_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_79C36135355AF43 ON suppliers_user (suppliers_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_79C36135A76ED395 ON suppliers_user (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, phone VARCHAR(20) DEFAULT NULL, username VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE affiliates ADD CONSTRAINT FK_108C6A8F783E3463 FOREIGN KEY (manager_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE assortiment ADD CONSTRAINT FK_7366C5784584665A FOREIGN KEY (product_id) REFERENCES products (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deliveries ADD CONSTRAINT FK_6F0785682ADD6D8C FOREIGN KEY (supplier_id) REFERENCES suppliers (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deliveries ADD CONSTRAINT FK_6F078568933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredients (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE32E0841B FOREIGN KEY (reciever_partner_id) REFERENCES partners (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE4584665A FOREIGN KEY (product_id) REFERENCES products (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE partners_user ADD CONSTRAINT FK_B3E4CF7ABDE7F1C6 FOREIGN KEY (partners_id) REFERENCES partners (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE partners_user ADD CONSTRAINT FK_B3E4CF7AA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE production_plan ADD CONSTRAINT FK_64A10694584665A FOREIGN KEY (product_id) REFERENCES products (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_movement ADD CONSTRAINT FK_E1DC85449F12C49A FOREIGN KEY (affiliate_id) REFERENCES affiliates (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_movement ADD CONSTRAINT FK_E1DC85444584665A FOREIGN KEY (product_id) REFERENCES products (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE suppliers_user ADD CONSTRAINT FK_79C36135355AF43 FOREIGN KEY (suppliers_id) REFERENCES suppliers (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE suppliers_user ADD CONSTRAINT FK_79C36135A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE affiliates DROP CONSTRAINT FK_108C6A8F783E3463
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE assortiment DROP CONSTRAINT FK_7366C5784584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deliveries DROP CONSTRAINT FK_6F0785682ADD6D8C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deliveries DROP CONSTRAINT FK_6F078568933FE08C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders DROP CONSTRAINT FK_E52FFDEE32E0841B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders DROP CONSTRAINT FK_E52FFDEE4584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE partners_user DROP CONSTRAINT FK_B3E4CF7ABDE7F1C6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE partners_user DROP CONSTRAINT FK_B3E4CF7AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE production_plan DROP CONSTRAINT FK_64A10694584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_movement DROP CONSTRAINT FK_E1DC85449F12C49A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products_movement DROP CONSTRAINT FK_E1DC85444584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE suppliers_user DROP CONSTRAINT FK_79C36135355AF43
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE suppliers_user DROP CONSTRAINT FK_79C36135A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE affiliates
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE assortiment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE deliveries
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ingredients
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE orders
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE partners
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE partners_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE production_plan
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE products
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE products_movement
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE suppliers
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE suppliers_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
    }
}
