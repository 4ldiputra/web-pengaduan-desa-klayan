#!/bin/sh
set -e

echo "Starting web-pengaduan-desa-klayan Application..."

# Install/update composer dependencies if vendor is empty or missing
if [ ! -d "/var/www/html/vendor" ] || [ -z "$(ls -A /var/www/html/vendor)" ]; then
    echo "Installing composer dependencies..."
    composer install --no-dev --no-interaction --optimize-autoloader --no-scripts
fi

# Check if .env exists
if [ ! -f /var/www/html/.env ]; then
    echo "ERROR: .env file not found!"
    exit 1
fi

# Check if APP_KEY is set
if ! grep -q "^APP_KEY=base64:" /var/www/html/.env; then
    echo "ERROR: APP_KEY not set!"
    exit 1
fi

# Wait for database to be ready
echo "Waiting for database..."
MAX_RETRIES=30
RETRY_COUNT=0

DB_HOST=$(grep "^DB_HOST=" /var/www/html/.env | cut -d '=' -f2)
DB_PORT=$(grep "^DB_PORT=" /var/www/html/.env | cut -d '=' -f2 || echo "3306")

until nc -z "$DB_HOST" "${DB_PORT:-3306}" 2>/dev/null || [ $RETRY_COUNT -eq $MAX_RETRIES ]; do
    echo "Database port not ready - sleeping (attempt $RETRY_COUNT/$MAX_RETRIES)"
    sleep 2
    RETRY_COUNT=$((RETRY_COUNT+1))
done

if [ $RETRY_COUNT -eq $MAX_RETRIES ]; then
    echo "ERROR: Could not connect to database after $MAX_RETRIES attempts"
    exit 1
fi

echo "Database is ready!"

# Error handlers
php -r "require '/var/www/html/vendor/autoload.php';" 2>&1 || {
    echo "ERROR: Autoloader failed"
    exit 1
}
grep "^APP_KEY=" /var/www/html/.env || {
    echo "ERROR: APP_KEY not found or empty in .env"
    exit 1
}

# Run migrations
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Running migrations..."
    php artisan migrate --force --no-interaction || exit 1
else
    echo "Skipping migrations (RUN_MIGRATIONS=false)"
fi

# Clear and cache configurations
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true

php artisan route:cache
php artisan view:cache

# Create storage link if it doesn't exist
if [ ! -L /var/www/html/public/storage ]; then
    echo "Storage link doesn't exist. Creating..."
    php artisan storage:link
fi

# Set permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Application is ready!"
exec "$@"
