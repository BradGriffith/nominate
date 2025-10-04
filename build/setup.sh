#!/bin/bash

set -e

echo "🚀 Setting up Nominate development environment..."

# Detect docker compose command (docker-compose vs docker compose)
if command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE="docker-compose"
elif docker compose version &> /dev/null 2>&1; then
    DOCKER_COMPOSE="docker compose"
else
    echo "❌ Docker Compose not found. Please install Docker Compose and try again."
    exit 1
fi

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker and try again."
    exit 1
fi

# Copy environment file if it doesn't exist
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.docker..."
    cp .env.docker .env
    echo "⚠️  Remember to update PUSHER credentials in .env!"
else
    echo "ℹ️  .env file already exists, skipping..."
fi

# Start Docker containers
echo "🐳 Starting Docker containers..."
$DOCKER_COMPOSE up -d

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 10

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
$DOCKER_COMPOSE exec -T app composer install --no-interaction

# Generate application key
echo "🔑 Generating application key..."
$DOCKER_COMPOSE exec -T app php artisan key:generate

# Run migrations
echo "📊 Running database migrations..."
$DOCKER_COMPOSE exec -T app php artisan migrate --force

# Set permissions
echo "🔒 Setting file permissions..."
$DOCKER_COMPOSE exec -T app chmod -R 775 storage bootstrap/cache
$DOCKER_COMPOSE exec -T app chown -R nominate:nominate storage bootstrap/cache

# Install and build frontend assets
echo "🎨 Installing npm dependencies and building assets..."
$DOCKER_COMPOSE exec -T node npm install
$DOCKER_COMPOSE exec -T node npm run dev

echo ""
echo "✅ Setup complete!"
echo ""
echo "📍 Your application is available at: http://localhost:8000"
echo ""
echo "Next steps:"
echo "1. Update Pusher credentials in .env"
echo "2. Run: $DOCKER_COMPOSE restart"
echo "3. (Optional) Set up Voyager admin:"
echo "   $DOCKER_COMPOSE exec app php artisan voyager:install"
echo "   $DOCKER_COMPOSE exec app php artisan voyager:admin your@email.com --create"
echo ""
echo "Useful commands:"
echo "  $DOCKER_COMPOSE logs -f app     # View logs"
echo "  $DOCKER_COMPOSE down            # Stop containers"
echo "  $DOCKER_COMPOSE exec app bash   # Access app container"
echo ""
