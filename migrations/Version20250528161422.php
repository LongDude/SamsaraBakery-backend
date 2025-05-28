<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528161422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE PROCEDURE deliver_product_to_affiliate(
                in_affiliate_id INT,
                in_product_id INT,
                in_count_recieved INT,
                in_date DATE DEFAULT CURRENT_DATE
            ) AS $$
            DECLARE
                assortiment_id INT;
                current_quantity INT;
            BEGIN
                -- 1. Добавить запись о движении товара
                INSERT INTO products_movement (affiliate_id, product_id, recieved_count, date)
                VALUES (in_affiliate_id, in_product_id, in_count_recieved, in_date);

                -- 2. Обновить ассортимент филиала
                SELECT id, quantity INTO assortiment_id, current_quantity
                FROM assortiment
                WHERE affiliate_id = in_affiliate_id AND product_id = in_product_id
                LIMIT 1;

                IF assortiment_id IS NULL THEN
                    RAISE EXCEPTION 'Нет записей о наличии продукта % в ассортименте филиала %', in_product_id, in_affiliate_id;
                END IF;

                UPDATE assortiment
                SET quantity = quantity + in_count_recieved
                WHERE id = assortiment_id;

                -- 3. Обновить склад (остатки) в products
                UPDATE products
                SET quantity_storaged = quantity_storaged - in_count_recieved
                WHERE id = in_product_id;

                IF NOT FOUND THEN
                    RAISE EXCEPTION 'Product not found or not enough stock for product %', in_product_id;
                END IF;
            END;
            $$ LANGUAGE plpgsql;
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP PROCEDURE IF EXISTS deliver_product_to_affiliate(INT, INT, INT, DATE);');
    }
}
