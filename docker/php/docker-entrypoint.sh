#!/bin/sh
set -e

# Wait for database to be ready
echo "Waiting for database to be ready..."
while ! nc -z database 5432; do
    sleep 1
done
echo "Database is ready!"

# List migrations status
echo "Checking migrations status..."
php bin/console doctrine:migrations:status

# Run migrations
echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Start Symfony server
echo "Starting Symfony server..."
symfony server:start --port=8000 --allow-http --no-tls --allow-all-ip 