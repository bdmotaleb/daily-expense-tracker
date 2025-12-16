#!/bin/bash

echo "Setting up Laravel application..."

# Install Composer dependencies
echo "Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Generate application key if not exists
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cp .env.example .env
fi

# Generate app key
php artisan key:generate --force

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
until php artisan migrate:status &> /dev/null; do
    echo "MySQL is unavailable - sleeping"
    sleep 2
done

echo "MySQL is up - executing migrations"

# Run migrations
php artisan migrate --force

# Seed database
php artisan db:seed --force

echo "Setup complete!"

