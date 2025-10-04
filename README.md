# Nomination & Voting System

A Laravel-based application for conducting multi-phase elections with voting and ranked-choice ranking.

## Overview

This system facilitates a three-phase election process:

1. **Voting Phase** - Users vote for their preferred nominees from a list of candidates
2. **Ranking Phase** - Users rank the top vote-getters in order of preference
3. **Results Phase** - Final results displayed based on ranked-choice voting (lowest sum of ranks wins)

## Features

- Real-time updates using Laravel Echo and Pusher
- Cookie-based voter tracking (no login required for voting/ranking)
- Admin dashboard for managing positions and viewing participation
- Automatic tie handling in vote counts
- Year-based election management

## Requirements

- PHP 8.1+
- Composer
- Node.js & NPM
- MySQL/PostgreSQL database
- Pusher account (for real-time features)

## Installation

### Using Docker (Recommended for Development)

```bash
# Initial setup
./dev setup

# Or use the old setup script
./build/setup.sh
```

This will set up a complete development environment with PHP, MySQL, Redis, and Nginx.

**Quick commands:**
```bash
./dev setup          # Initial setup
./dev fresh          # Reset database with test data
./dev db             # Enter database CLI
./dev shell          # Enter app container
./dev artisan <cmd>  # Run artisan commands
```

See [build/README.md](build/README.md) for detailed Docker documentation.

### Manual Installation

1. Clone the repository and install dependencies:
```bash
composer install
npm install
```

2. Configure environment:
```bash
cp .env.example .env
php artisan key:generate
```

3. Update `.env` with your database and Pusher credentials:
```
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster

VOTER_COUNT=24  # Number of voters
```

4. Run migrations:
```bash
php artisan migrate
```

5. Build assets:
```bash
npm run dev
```

## Development

### Docker (./dev script)

The `./dev` script provides a complete management interface:

```bash
# Setup & Management
./dev setup              # Initial setup
./dev start              # Start containers
./dev stop               # Stop containers
./dev restart            # Restart containers
./dev logs [service]     # View logs

# Database
./dev db                 # Enter MySQL CLI
./dev migrate            # Run migrations
./dev fresh              # Reset database with test data
./dev seed               # Run seeders
./dev seed:fake          # Seed fake test data

# Shell Access
./dev shell              # Bash shell in app container
./dev tinker             # Laravel Tinker REPL
./dev artisan <cmd>      # Run artisan command
./dev composer <cmd>     # Run composer command
./dev npm <cmd>          # Run npm command

# Development
./dev build              # Build production assets
./dev dev                # Build development assets
./dev watch              # Watch and rebuild assets
./dev test               # Run PHPUnit tests
```

**Via npm:**
```bash
npm run docker setup     # Same as ./dev setup
npm run docker fresh     # Same as ./dev fresh
```

### Manual (Non-Docker)

```bash
# Start development server
php artisan serve

# Watch and compile frontend assets
npm run watch

# Build for production
npm run prod
```

## Usage

### Setting Up an Election

1. Access admin panel at `/admin` (requires authentication)
2. Create a new Position (e.g., "President 2025")
3. Add Nominations for that position
4. Set the position as default to make it active

### Running the Election

**Phase 1: Voting**
- Users visit `/vote`
- Select their voter number (1-N)
- Choose nominees to vote for
- System tracks who has voted

**Phase 2: Ranking**
- Admin changes position status to "rank" via Dashboard
- System automatically determines top nominees (including ties)
- Users visit `/rank` to order their preferences
- Lower rank number = higher preference

**Phase 3: Results**
- Admin changes position status to "results"
- Users redirected to `/results`
- Winners determined by lowest sum of ranks

### Admin Functions

- View voters/rankers who have participated
- Delete votes or rankings for specific users
- Change number of nominees to select
- Control election phase transitions

## Testing

```bash
php artisan test
```

## License

MIT License
