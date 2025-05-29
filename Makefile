.PHONY: create-migration migrate migrate-down load-fixtures install-prereq build deploy down project-init 

help:
	@echo " Doctrine commands "
	@echo "		create-migration - Compare database with local schema and create migration"
	@echo "		migrate - Apply all migrations"
	@echo "		migrate-down - Revert last migration"
	@echo "		load-fixtures - Load fake data"
	@echo " Service launch commands"
	@echo "		install-prereq - install components"
	@echo "		build - install components, load database & fixtures"
	@echo "		deploy - Launch docker compose with production container "
	@echo "		down - Service down"
	@echo "		project-init - initialize components and database"

### Doctrine ###
create-migration:
	symfony console doctrine:migration:diff
migrate:
	symfony console doctrine:migration:migrate
migrate-down:
	symfony console doctrine:migration:migrate prev
load-fixtures:
	symfony console doctrine:fixtures:load --purge-with-truncate \
	--purge-exclusions director_affiliate_finance_view \
	--purge-exclusions director_production_report_summary_view \
	--purge-exclusions director_production_report_view \
	--purge-exclusions director_orders_view \
	--purge-exclusions director_production_view \
	--purge-exclusions user_products_view

### Service ###
install-prereq:
	composer install

build:
	docker compose build

deploy:
	docker compose up -d

down:
	docker compose down

project-init: install-prereq build deploy migrate load-fixtures down 
