DOCKER_PHP=docker compose run php-fpm

DOCTRINE_BIN_PATH=src/Infrastructure/Persistence/Doctrine/bin.php
WORKER_PATH=src/Infrastructure/Queue/RabbitMQ/Worker.php

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
	$(DOCKER_PHP) composer install

.PHONY: migrate
migrate:
	$(DOCKER_PHP) php $(DOCTRINE_BIN_PATH) migrations:migrate

.PHONY: migration-generate
migration-generate:
	$(DOCKER_PHP) php $(DOCTRINE_BIN_PATH) migrations:generate

.PHONY: migration-diff
migration-diff:
	$(DOCKER_PHP) php $(DOCTRINE_BIN_PATH) migrations:diff

.PHONY: test
test:
	$(DOCKER_PHP) composer test

.PHONY: queue-worker
queue-worker:
	$(DOCKER_PHP) php $(WORKER_PATH)
