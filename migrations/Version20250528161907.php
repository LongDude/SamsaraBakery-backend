<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528161907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE FUNCTION set_movement_prices()
            RETURNS TRIGGER AS $$
            DECLARE
                prod_cost FLOAT;
                assort_price FLOAT;
            BEGIN
                -- Если поступление (recieved_count > 0) и не задан recieved_cost, подставить себестоимость
                IF NEW.recieved_count > 0 AND (NEW.recieved_cost IS NULL OR NEW.recieved_cost = 0) THEN
                    SELECT production_cost INTO prod_cost FROM products WHERE id = NEW.product_id;
                    IF prod_cost IS NOT NULL THEN
                        NEW.recieved_cost := prod_cost * NEW.recieved_count;
                    END IF;
                END IF;

                -- Если реализация (realised_count > 0) и не задан realised_price, подставить цену из ассортимента
                IF NEW.realised_count > 0 AND (NEW.realised_price IS NULL OR NEW.realised_price = 0) THEN
                    SELECT price INTO assort_price FROM assortiment
                    WHERE affiliate_id = NEW.affiliate_id AND product_id = NEW.product_id
                    LIMIT 1;
                    IF assort_price IS NOT NULL THEN
                        NEW.realised_price := assort_price * NEW.realised_count;
                    END IF;
                END IF;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TRIGGER trg_set_movement_prices
            BEFORE INSERT OR UPDATE ON products_movement
            FOR EACH ROW
            EXECUTE FUNCTION set_movement_prices();
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TRIGGER IF EXISTS trg_set_movement_prices ON products_movement;');
        $this->addSql('DROP FUNCTION IF EXISTS set_movement_prices();');
    }
}
