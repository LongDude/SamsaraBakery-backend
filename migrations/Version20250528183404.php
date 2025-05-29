<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528183404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // 8.1  - Director - Управление списком заказов
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE VIEW director_orders_view AS
            SELECT
                o.id AS order_id,
                p.firmname AS partner_firmname,
                pr.name AS product,
                o.price,
                o.quantity,
                o.status,
                o.date
            FROM
                orders o
            JOIN
                partners p ON o.reciever_partner_id = p.id
            JOIN
                products pr ON o.product_id = pr.id;
        SQL);
        
        // 8.2  - Director - Управление филиалами
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE VIEW director_affiliate_finance_view AS
            SELECT
                a.id AS affiliate_id,
                a.address AS affiliate_address,
                a.contact_number,
                u.id AS manager_id,
                u.username AS manager_name,
                u.phone AS manager_phone,
                pm.date::date AS day,
                COALESCE(SUM(pm.realised_price), 0) AS revenue,
                COALESCE(SUM(pm.recieved_cost), 0) AS cost,
                COALESCE(SUM(pm.realised_price), 0) - COALESCE(SUM(pm.recieved_cost), 0) AS net_revenue
            FROM
                affiliates a
            LEFT JOIN "user" u ON u.id = a.manager_id
            LEFT JOIN products_movement pm ON pm.affiliate_id = a.id
            GROUP BY
                a.id, a.address, a.contact_number, u.id, u.username, u.phone, pm.date::date
            ORDER BY
                a.id, day;
        SQL);

        // 8.3 - Director - Добавление продукции
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE VIEW director_production_view AS
            SELECT
                id AS product_id,
                name AS product_name,
                production_cost
            FROM
                products;
        SQL);

        // 8.4 - Director - Отчет о продажах (c датами)
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE VIEW director_production_report_view AS
            SELECT
                row_number() OVER (ORDER BY p.name, COALESCE(pm.date, o.date)) AS id,
                p.name AS product_name,
                COALESCE(pm.date, o.date) AS date,
                COALESCE(SUM(pm.realised_price), 0) AS sells_revenue,
                COALESCE(SUM(CASE WHEN o.status = 'Order delivered' THEN o.price ELSE 0 END), 0) AS orders_revenue,
                -- Себестоимость: поступившее + отправленное по заказам
                COALESCE(SUM(pm.recieved_cost), 0) +
                    COALESCE(SUM(CASE WHEN o.status = 'Order delivered' THEN p.production_cost * o.quantity ELSE 0 END), 0) AS production_cost,
                -- Поступило: в филиалы + отправлено партнёрам по закрытым заказам
                COALESCE(SUM(pm.recieved_count), 0) +
                    COALESCE(SUM(CASE WHEN o.status = 'Order delivered' THEN o.quantity ELSE 0 END), 0) AS producted_count,
                COALESCE(SUM(pm.realised_count), 0) AS sold_count,
                COALESCE(SUM(CASE WHEN o.status = 'Order delivered' THEN o.quantity ELSE 0 END), 0) AS ordered_count,
                CASE
                    WHEN (COALESCE(SUM(pm.recieved_count), 0) + COALESCE(SUM(CASE WHEN o.status = 'Order delivered' THEN o.quantity ELSE 0 END), 0)) > 0
                    THEN ROUND(COALESCE(SUM(pm.realised_count), 0)::numeric /
                            (COALESCE(SUM(pm.recieved_count), 0) + COALESCE(SUM(CASE WHEN o.status = 'Order delivered' THEN o.quantity ELSE 0 END), 0)) * 100, 2)
                    ELSE 0
                END AS realisation_index,
                CASE
                    WHEN (COALESCE(SUM(pm.recieved_count), 0) + COALESCE(SUM(CASE WHEN o.status = 'Order delivered' THEN o.quantity ELSE 0 END), 0)) > 0
                    THEN ROUND(COALESCE(SUM(CASE WHEN o.status = 'Order delivered' THEN o.quantity ELSE 0 END), 0)::numeric /
                            (COALESCE(SUM(pm.recieved_count), 0) + COALESCE(SUM(CASE WHEN o.status = 'Order delivered' THEN o.quantity ELSE 0 END), 0)) * 100, 2)
                    ELSE 0
                END AS order_index,
                (COALESCE(SUM(pm.realised_price), 0) + COALESCE(SUM(CASE WHEN o.status = 'Order delivered' THEN o.price ELSE 0 END), 0))
                    - (COALESCE(SUM(pm.recieved_cost), 0) + COALESCE(SUM(CASE WHEN o.status = 'Order delivered' THEN p.production_cost * o.quantity ELSE 0 END), 0))
                    AS net_revenue
            FROM
                products p
            LEFT JOIN products_movement pm ON pm.product_id = p.id
            RIGHT JOIN orders o ON o.product_id = p.id
            GROUP BY
                p.name, COALESCE(pm.date, o.date);
        SQL);

        // 8.4+ - Director - Отчет о продажах (без дат)
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE VIEW director_production_report_summary_view AS
            SELECT
                row_number() OVER (ORDER BY product_name) AS id,
                product_name,
                SUM(sells_revenue) AS sells_revenue,
                SUM(orders_revenue) AS orders_revenue,
                SUM(production_cost) AS production_cost,
                SUM(producted_count) AS producted_count,
                SUM(sold_count) AS sold_count,
                SUM(ordered_count) AS ordered_count,
                CASE
                    WHEN SUM(producted_count) > 0
                    THEN ROUND(SUM(sold_count)::numeric / SUM(producted_count) * 100, 2)
                    ELSE 0
                END AS realisation_index,
                CASE
                    WHEN SUM(producted_count) > 0
                    THEN ROUND(SUM(ordered_count)::numeric / SUM(producted_count) * 100, 2)
                    ELSE 0
                END AS order_index,
                SUM(net_revenue) AS net_revenue
            FROM director_production_report_view
            GROUP BY product_name;
        SQL);

        // 9.1 - User - Управление продукцией
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE VIEW user_products_view AS
            SELECT
                name AS product,
                production_cost*1.2 AS price,
                quantity_storaged AS quantity
            FROM
                products;
        SQL);

    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP VIEW director_orders_view');
        $this->addSql('DROP VIEW director_affiliate_finance_view');
        $this->addSql('DROP VIEW director_production_view');
        $this->addSql('DROP VIEW director_production_report_summary_view');
        $this->addSql('DROP VIEW director_production_report_view');
        $this->addSql('DROP VIEW user_products_view');
    }
}
