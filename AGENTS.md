# AGENTS.md

This file provides guidance to coding agents when working with code in this repository.

## What this is

Sharemeister is a self-hosted, ShareX-compatible screenshot/image sharing service built on Laravel 12 (PHP 8.2+). Users upload images via the web UI or the API; images are converted to WebP, deduplicated per user, and served under short random URLs. It is distributed as a Docker image (`ghcr.io/flymia/sharemeister`).

Stack: Laravel 12, Blade + Tailwind CSS + Vite, MariaDB (default; also supports MySQL/Postgres/SQLite), Laravel Fortify (web auth + 2FA), Laravel Sanctum (API tokens), GD/`intervention/image` for image processing.

It uses Bootstrap 5 and does not compile the CSS using NPM or something.

## Commands

The whole dev environment (app, MariaDB, phpMyAdmin, smtp4dev) runs from a single compose file. Start it from the repo root:

```bash
docker compose -f Docker/docker-compose.yml up --build
```

This waits for the database, runs migrations automatically on first startup, and serves the app at http://localhost:8000 (phpMyAdmin on :8080, mail UI on :8081).

All dev related commands run inside the `app` container. With the stack running, exec into it:

```bash
docker compose -f Docker/docker-compose.yml exec app php artisan migrate
```

For one-off commands without the stack running, use `run --rm` instead:

```bash
docker compose -f Docker/docker-compose.yml run --rm app php artisan migrate
```

### Custom artisan commands

```bash
php artisan sharemeister:install                 # first-time setup: migrates, creates admin, writes .env
php artisan sharemeister:user ...                # user management (create/list/modify)
php artisan sharemeister:import {path} {email}   # bulk import a folder; or --csv=path.csv
php artisan sharemeister:clear-user-storage {email} [--force]
```

## Architecture

### Installation gate
`App\Http\Middleware\CheckInstallation` is appended globally in `bootstrap/app.php`. If no admin user exists (`is_admin = true`), every request redirects to `/setup-required`. Once an admin exists, the setup route redirects away. First admin is created by `sharemeister:install`, which also persists settings (`APP_NAME`, `DEFAULT_STORAGE_LIMIT`) by rewriting `.env` directly.

### Upload pipeline (the core of the app)
`App\Services\ScreenshotService::handleUpload($file, $user)` is the single entry point shared by the web UI, the API, and CLI import. It accepts **either** an `UploadedFile` (web/API) **or** a path string (CLI import) and branches on `is_string($file)` throughout. Steps:
1. **Per-user dedup** - SHA-256 hash of the original file; if the same user already uploaded it, the existing `Screenshot` is `touch()`ed and returned (no reprocessing, no extra storage).
2. **Size + quota checks** - `config('app.max_upload_size')` (KB, `MAX_UPLOAD_SIZE_KB`, default 10240) and per-user `storage_limit_mb` (`-1` = unlimited).
3. **WebP conversion** - everything except GIFs is re-encoded to WebP quality 80 via GD (GIFs are copied as-is to preserve animation). Files land under `storage/app/public/screenshots/{userId}/{Y}/{m}/{d}/` with an 8-char random name; a collision loop guarantees uniqueness.

`ScreenshotController::handleUpload()` wraps the service and converts thrown exceptions into `abort(403, ...)`.

### Screenshots model
`App\Models\Screenshot` uses **UUID primary keys** (`HasUuids`). It appends a `public_url` accessor that always points to `/screenshots/{basename}` (the `screenshot.raw` route), regardless of the stored disk path. `is_permanent` protects a screenshot from deletion (enforced in `destroy()`). Tags are many-to-many; `syncTags(string)` parses a comma-separated string into `Tag` records.

### Routing & auth
- `routes/web.php` - public landing + auth'd screenshot/account routes (`auth`, `verified` middleware). The raw image route `/screenshots/{filename}` is intentionally public.
- `routes/api.php` - `/api/upload` (JSON) and `/api/upload/raw` (plaintext link, for ShareX), protected by Sanctum tokens. Users generate API keys under account settings and can download a ready-made `.sxcu` config or bash upload script.
- Authorization is centralized in `App\Policies\ScreenshotPolicy` (owner-only view/update/delete), invoked via `$this->authorize(...)`.

### Fortify
`App\Providers\FortifyServiceProvider` wires auth views (`resources/views/auth/*`), rate limiting (5/min on login), and the `App\Actions\Fortify\*` action classes (registration, password reset/update, profile update). 2FA columns exist on `users`.

### Services layer
Controllers stay thin; logic lives in `App\Services` (`ScreenshotService`, `DashboardService`, `HealthService`). Health/metrics: `HealthController` (`/health`) and `Api\SystemMetricsController` (`/api/health`) expose instance stats.

## Deployment

Production image is built from `Docker/Production/Dockerfile` (nginx + php-fpm; see `Docker/Production/docker-compose.yml` and `nginx.conf`). CI (`.github/workflows/docker-release.yml`) builds multi-arch (amd64/arm64) images and pushes to GHCR **on GitHub release publish only** - not on push. `Docker/Dockerfile` + root `docker-compose.yml` are for local development.
