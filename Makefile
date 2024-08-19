.PHONY: install
install: env up composer-install migrate
	@echo "Your environment is ready to use!"

.PHONY: up
up:
	@echo "Starting all docker containers"
	docker compose up --force-recreate -d

.PHONY: down
down:
	@echo "Stopping all docker containers"
	docker compose down

.PHONY: restart
restart: down up
	@echo "Restating all docker containers"

.PHONY: env
env:
	@echo "Copying .env.example to .env file"
	@cp .env.example .env

.PHONY: composer-install
composer-install:
	@echo "Installing composer dependencies"
	docker compose run composer install

.PHONY: migrate
migrate:
	docker compose run php-fpm php src/Infrastructure/Persistence/Doctrine/bin.php migrations:migrate

.PHONY: create-migration
migration-generate:
	docker compose run php-fpm php src/Infrastructure/Persistence/Doctrine/bin.php migrations:generate

.PHONY: test
test:
	docker compose run composer test
