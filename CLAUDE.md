# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a nomination and voting application built with Laravel 8 and Vue 2 (using Inertia.js). The system allows users to:
1. **Vote** for nominees from a list of candidates
2. **Rank** the top nominees (those who received the most votes)
3. View **Results** based on ranked-choice voting (sum of ranks, lower is better)

The application uses Laravel Jetstream for authentication and Voyager for admin functionality.

## Technology Stack

- **Backend**: Laravel 8 (PHP 8.1+)
- **Frontend**: Vue 2 with Inertia.js
- **Styling**: TailwindCSS
- **Real-time**: Laravel Echo with Pusher for live updates
- **Admin Panel**: Voyager (accessible at `/admin`)

## Development Commands

### Installation & Setup
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### Development
```bash
# Run dev server
php artisan serve

# Watch and compile assets
npm run watch

# Build for production
npm run prod
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test suite
php vendor/bin/phpunit --testsuite=Feature
php vendor/bin/phpunit --testsuite=Unit
```

## Architecture

### Core Workflow

The application follows a three-phase process controlled by the `Position` model's `status` field:

1. **vote** - Users select nominees from a list
2. **rank** - Users rank the top nominees (based on vote counts)
3. **results** - Display final rankings based on sum of ranks

### Key Models & Relationships

- **Position**: Represents an election position (e.g., "President")
  - `is_default` - Currently active position
  - `status` - Current phase: 'vote', 'rank', or 'results'
  - `num_to_select` - Number of nominees users can vote for

- **Nomination**: Individual candidates for a position
  - Has many `Vote` and `Ranking` records
  - `getNomineesForRanking()` - Returns top N nominees with most votes (including ties)

- **Vote**: User votes for nominees
  - Tracks `voter` (number 1-N based on config)
  - Linked to `position_id` and `nomination_id`

- **Ranking**: User rankings of nominees
  - Lower rank number = better (rank 1 is best)
  - `getWinners()` - Sums ranks per nominee, orders ascending

### Real-time Updates

The app uses Laravel Broadcasting with Pusher:
- `PositionEvent` - Broadcast when position status changes
- `ResultsEvent` - Broadcast when votes/rankings change
- Frontend components listen via Echo and auto-navigate between phases

### Voter Management

- Voter count configured in `config/fcc.php` (default: 24)
- Voters identified by number (1-N), stored in cookies
- `Vote::getVotedVoters()` and `Ranking::getRankedVoters()` track who has participated

### Frontend Structure

- **Inertia.js** for SPA-like experience without API
- Pages in `resources/js/Pages/`:
  - `VotePage.vue` - Voting interface
  - `RankPage.vue` - Ranking interface
  - `ResultsPage.vue` - Results display
  - `Dashboard.vue` - Admin controls (authenticated)

### API Routes

All API routes in `routes/api.php`:
- RESTful resources: `votes`, `positions`, `ranks`, `nominations`
- Custom endpoints:
  - `PUT /api/positions/default` - Set active position
  - `GET /api/voters/{position_id}` - Get available voter numbers
  - `GET /api/rankers/{position_id}` - Get available ranker numbers
  - `DELETE /api/votes/{position_id}/{voter}` - Admin delete votes
  - `DELETE /api/ranks/{position_id}/{ranker}` - Admin delete ranks

### Important Patterns

1. **Year-based filtering**: All queries filter by `date('Y')` for current year
2. **Tie handling**: When multiple nominees tie for Nth place in votes, all are included in ranking phase
3. **Auto-navigation**: Vue components listen for position status changes and redirect users to appropriate page
4. **Model events**: Position, Vote, and Ranking models broadcast events on save/update
