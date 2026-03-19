# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Context

**Always load these files into context before starting any work:**
- `docs/TASK.md` — recruitment task requirements (4 tasks: code quality, photo import, filtering, rate-limiting)
- `docs/NOTES.md` — developer's architectural decisions, assumptions, in-progress checklist, and AI usage notes

## Architecture

Two independent services, each with its own PostgreSQL database:

- **symfony-app** (PHP 8.1 / Symfony 6.4, port 8000) — main web app (MVC)
  - `src/Controller/` — HTTP controllers
  - `src/Entity/` — Doctrine ORM entities (`User`, `Photo`, `AuthToken`)
  - `src/Repository/` — Doctrine repositories (`PhotoRepository`)
  - `src/Likes/` — Like component (`Like`, `LikeRepository`, `LikeRepositoryInterface`, `LikeService`)
  - `migrations/` — Doctrine migrations
  - `tests/` — PHPUnit tests

- **phoenix-api** (Elixir / Phoenix 1.7, port 4000) — REST API microservice
  - `lib/phoenix_api/accounts/` — `User` context
  - `lib/phoenix_api/media/` — `Photo` context
  - `lib/phoenix_api_web/controllers/` — `PhotoController` (GET `/api/photos`)
  - `lib/phoenix_api_web/plugs/` — `Authenticate` plug

Symfony communicates with PhoenixApi via `PHOENIX_BASE_URL` env var (internal Docker: `http://phoenix:4000`).

## Running Commands (via Docker)

All commands must be executed against running containers using `docker compose exec`:

### Start

```bash
docker compose up -d
```

### Symfony App

```bash
# Run all tests
docker compose exec symfony php bin/phpunit

# Run a single test file
docker compose exec symfony php bin/phpunit tests/path/to/TestFile.php

# Run a single test method
docker compose exec symfony php bin/phpunit --filter testMethodName

# Doctrine migrations
docker compose exec symfony php bin/console doctrine:migrations:migrate --no-interaction

# Clear cache
docker compose exec symfony php bin/console cache:clear

# Recreate database
docker compose exec symfony php bin/console doctrine:schema:drop --force --full-database
docker compose exec symfony php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec symfony php bin/console app:seed
```

### Phoenix API

```bash
# Run all tests
docker compose exec phoenix mix test

# Run a single test file
docker compose exec phoenix mix test test/path/to/test_file.exs

# Run migrations
docker compose exec phoenix mix ecto.migrate

# Seed database
docker compose exec phoenix mix run priv/repo/seeds.exs

# Recreate database
docker compose exec phoenix mix ecto.reset
docker compose exec phoenix mix run priv/repo/seeds.exs
```

## Key Notes from Developer

- MVC architecture is intentional given project scope; hexagonal architecture was considered but deemed overengineering here.
- Direct model-to-view passing is flagged; DTO layer is preferred.