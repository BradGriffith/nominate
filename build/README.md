# Docker Development Environment

This directory contains Docker configuration for local development.

> **Note:** Commands use `docker compose` (Docker Compose V2) or `docker-compose` (V1) depending on your installation.

## Quick Start

1. **Copy environment file:**
   ```bash
   cp .env.docker .env
   ```

2. **Update Pusher credentials in `.env`:**
   - Sign up at https://pusher.com if you don't have an account
   - Create a new app and copy credentials to `.env`

3. **Start Docker containers:**
   ```bash
   docker compose up -d
   # OR: docker-compose up -d
   ```

4. **Install dependencies:**
   ```bash
   docker compose exec app composer install
   ```

5. **Generate application key:**
   ```bash
   docker compose exec app php artisan key:generate
   ```

6. **Run migrations:**
   ```bash
   docker compose exec app php artisan migrate
   ```

7. **Build frontend assets:**
   The `node` container will automatically watch and rebuild assets.
   If you need to build manually:
   ```bash
   docker compose exec node npm run dev
   ```

8. **Access the application:**
   - Web: http://localhost:8000
   - MySQL: localhost:3306 (user: nominate, password: secret)
   - Redis: localhost:6379

## Common Commands

> Replace `docker compose` with `docker-compose` if using V1.

```bash
# View logs
docker compose logs -f app

# Run artisan commands
docker compose exec app php artisan [command]

# Run composer commands
docker compose exec app composer [command]

# Access MySQL
docker compose exec db mysql -u nominate -psecret nominate

# Stop containers
docker compose down

# Stop and remove volumes (delete database)
docker compose down -v
```

## Voyager Admin Setup

1. Install Voyager:
   ```bash
   docker compose exec app php artisan voyager:install --with-dummy
   ```

2. Create admin user:
   ```bash
   docker compose exec app php artisan voyager:admin your@email.com --create
   ```

3. Access admin panel at http://localhost:8000/admin

## Troubleshooting

**Permission issues:**
```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R nominate:nominate storage bootstrap/cache
```

**Clear cache:**
```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan view:clear
```

**Rebuild containers:**
```bash
docker compose down
docker compose build --no-cache
docker compose up -d
```
