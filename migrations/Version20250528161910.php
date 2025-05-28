<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528161910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE FUNCTION update_ingredient_quantity_on_delivery()
            RETURNS TRIGGER AS $$
            BEGIN
                -- Проверяем, что статус изменился на 'Ingredients received'
                IF NEW.status = 'Ingredients received' AND (OLD.status IS DISTINCT FROM NEW.status) THEN
                    UPDATE ingredients
                    SET quantity = quantity + NEW.quantity
                    WHERE id = NEW.ingredient_id;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TRIGGER trg_update_ingredient_quantity_on_delivery
            AFTER UPDATE ON deliveries
            FOR EACH ROW
            EXECUTE FUNCTION update_ingredient_quantity_on_delivery();
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TRIGGER IF EXISTS trg_update_ingredient_quantity_on_delivery ON deliveries;
            DROP FUNCTION IF EXISTS update_ingredient_quantity_on_delivery();
        SQL);
    }
}
