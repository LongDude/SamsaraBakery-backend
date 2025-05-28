<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528161912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE FUNCTION validate_order_status_change()
            RETURNS TRIGGER AS $$
            DECLARE
                current_stock INT;
            BEGIN
                -- 0. Проверим, что заказ не закрыт и изменяется только статус
                IF OLD.status = 'Order closed'::text OR OLD.status = 'Order delivered'::text THEN
                    RAISE EXCEPTION 'Нельзя изменить статус заказа, который уже закрыт или доставлен';
                END IF;

                -- Проверяем, изменяются ли другие поля кроме статуса
                IF (OLD.product_id IS DISTINCT FROM NEW.product_id) OR
                    (OLD.reciever_partner_id IS DISTINCT FROM NEW.reciever_partner_id) OR
                    (OLD.price IS DISTINCT FROM NEW.price) OR
                    (OLD.quantity IS DISTINCT FROM NEW.quantity) OR
                    (OLD.date IS DISTINCT FROM NEW.date) THEN
                    RETURN NEW;
                END IF;

                -- Превращаем статус в машину состояний: задаем правила изменения статуса
                -- 1. После подтверждения заказа его можно перевести только в IN_STORAGE или IN_PRDUCTION
                IF OLD.status = 'Order approved'::text AND 
                    NEW.status <> 'Order in production'::text AND
                    NEW.status <> 'Order is in storage'::text AND
                    NEW.status <> 'Order approved'::text THEN
                    RAISE EXCEPTION 'Нельзя изменить статус с состяния Принято на % - только в % или %', NEW.status, 'Order in production', 'Order is in storage';
                END IF;

                -- 2. После начала производства заказ можно перевести только в IN_STORAGE
                IF OLD.status = 'Order in production'::text AND 
                    NEW.status <> 'Order is in storage'::text AND
                    NEW.status <> 'Order in production'::text THEN
                    RAISE EXCEPTION 'Нельзя изменить статус с состяния Производство на % - только в %', NEW.status, 'Order is in storage';
                END IF;

                -- 3 В IN_STORAGE можно перейти только если достаточно товара на складе
                IF OLD.status = 'Order in production'::text AND 
                    NEW.status = 'Order is in storage'::text AND 
                    (SELECT quantity_storaged FROM products WHERE id = NEW.product_id) < NEW.quantity THEN
                    RAISE EXCEPTION 'Нельзя изменить статус с состяния Производство на На складе, так как нет достаточного количества товара на складе';
                END IF;

                -- 4. После перевода в IN_STORAGE заказ можно перевести только в BEING_DELIVERED
                IF OLD.status = 'Order is in storage'::text AND 
                    NEW.status = 'Order being delivered'::text THEN
                    SELECT quantity_storaged INTO current_stock 
                    FROM products 
                    WHERE id = NEW.product_id;
                    
                    IF current_stock < NEW.quantity THEN
                        RAISE EXCEPTION 'Недостаточно товара на складе для заказа % (есть %, нужно %)', 
                                        NEW.product_id, current_stock, NEW.quantity;
                    END IF;
                END IF;

                -- 5. Когда переводим из IN_STORAGE в BEING_DELIVERED вычитаем отгруженный товар
                IF OLD.status = 'Order is in storage'::text AND 
                NEW.status = 'Order being delivered'::text THEN
                    -- Проверим, что достаточно товара на складе
                    SELECT quantity_storaged INTO current_stock 
                    FROM products 
                    WHERE id = NEW.product_id;

                    IF current_stock < NEW.quantity THEN
                        RAISE EXCEPTION 'Недостаточно товара на складе для заказа % (есть %, нужно %)', 
                                        OLD.id, current_stock, NEW.quantity;
                    END IF;
                    
                    -- Вычитаем отгруженный товар
                    UPDATE products 
                    SET quantity_storaged = quantity_storaged - NEW.quantity 
                    WHERE id = NEW.product_id;
                END IF;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TRIGGER order_status_validation
            BEFORE UPDATE ON orders
            FOR EACH ROW
            WHEN (OLD.status IS DISTINCT FROM NEW.status)
            EXECUTE FUNCTION validate_order_status_change();
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TRIGGER IF EXISTS order_status_validation ON orders;
            DROP FUNCTION IF EXISTS validate_order_status_change();
        SQL);
    }
}
