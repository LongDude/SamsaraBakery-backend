<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528161654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE PROCEDURE sell_product_from_affiliate(
                in_affiliate_id INT,
                in_product_id INT,
                in_count_sold INT,
                in_date DATE DEFAULT CURRENT_DATE
            ) AS $$
            DECLARE
                assortiment_id INT;
                current_quantity INT;
            BEGIN
                -- 1. Найти ассортимент
                SELECT id, quantity INTO assortiment_id, current_quantity
                FROM assortiment
                WHERE affiliate_id = in_affiliate_id AND product_id = in_product_id
                LIMIT 1;

                IF assortiment_id IS NULL THEN
                    RAISE EXCEPTION 'Нет записей о наличии продукта % в ассортименте филиала %', in_product_id, in_affiliate_id;
                END IF;

                IF current_quantity < in_count_sold THEN
                    RAISE EXCEPTION 'Недостаточно продукта в ассортименте для продажи (есть %, нужно %)', current_quantity, in_count_sold;
                END IF;

                -- 2. Обновить ассортимент (уменьшить количество)
                UPDATE assortiment
                SET quantity = quantity - in_count_sold
                WHERE id = assortiment_id;

                -- 3. Добавить запись о движении товара (реализация)
                INSERT INTO products_movement (affiliate_id, product_id, realised_count, date)
                VALUES (in_affiliate_id, in_product_id, in_count_sold, in_date);

            END;
            $$ LANGUAGE plpgsql;
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP PROCEDURE IF EXISTS sell_product_from_affiliate(INT, INT, INT, DATE);');
    }
}
