.PHONY: help build up down restart logs shell composer migrate seed test

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Build Docker containers
	docker-compose build

up: ## Start Docker containers
	docker-compose up -d

down: ## Stop Docker containers
	docker-compose down

restart: ## Restart Docker containers
	docker-compose restart

logs: ## Show Docker logs
	docker-compose logs -f

shell: ## Access app container shell
	docker-compose exec app bash

composer: ## Install Composer dependencies
	docker-compose exec app composer install

migrate: ## Run database migrations
	docker-compose exec app php artisan migrate

seed: ## Seed database
	docker-compose exec app php artisan db:seed

fresh: ## Fresh migration with seeding
	docker-compose exec app php artisan migrate:fresh --seed

test: ## Run tests
	docker-compose exec app php artisan test

setup: ## Initial setup (install dependencies, generate key, migrate, seed)
	docker-compose exec app composer install
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate
	docker-compose exec app php artisan db:seed

clean: ## Stop containers and remove volumes
	docker-compose down -v

