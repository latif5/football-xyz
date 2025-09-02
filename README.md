# XYZ Football API

Backend REST API built with Laravel 12 for managing football teams, players, fixtures, and results. Includes JWT authentication, soft deletes, validation, transactional result finalization, and analytics reports. Octane (RoadRunner) is available for high-performance serving.

## Features

- Teams CRUD with logo upload (public storage), soft delete/restore
- Players CRUD scoped to a team with shirt number uniqueness per team
- Matches (fixtures) creation/update, status tracking
- Results finalization (transactional), immutable after finish, goal records per minute
- Reports: match report, top scorers, cumulative team wins
- JWT auth-protected API with rate limiting

## Requirements

- PHP 8.2+
- Composer
- PostgreSQL (running locally)
- Node optional (not required to run API)

## Quick start

1) Configure environment (`.env`):

```
APP_NAME=XYZ Football API
APP_ENV=local
APP_KEY=base64:generated
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
TIMEZONE=Asia/Jakarta

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=football_xyz
DB_USERNAME=postgres
DB_PASSWORD=

# Recommended local dev settings
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

2) Install PHP dependencies:

```
composer install
```

3) Migrate and seed:

```
php artisan migrate --seed
```

4) Install JWT (already added to this repo):

```
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

5) Storage symlink (for logos):

```
php artisan storage:link
```

6) Run the app

- Development server:

```
php artisan serve
```

- High performance (Octane + RoadRunner):

```
php artisan octane:start --server=roadrunner
```

## Authentication

- Default admin user is seeded:
  - email: `admin`
  - password: `passwordDefault`

Login to obtain JWT token:

```
POST /api/auth/login
{
  "email": "admin",
  "password": "passwordDefault"
}
```

Use the token in subsequent requests:

```
Authorization: Bearer <token>
```

## API Endpoints (summary)

- Public:
  - GET `/api/health`
  - GET `/api/ping`
  - POST `/api/auth/login`

- Protected (JWT required):
  - Teams: GET/POST `/api/teams`, GET/PUT/DELETE `/api/teams/{team}`, POST `/api/teams/{team}/restore`
  - Players: GET/POST `/api/teams/{team}/players`, PUT/DELETE `/api/teams/{team}/players/{player}`
  - Matches: GET/POST `/api/matches`, GET/PUT `/api/matches/{match}`, POST `/api/matches/{match}/finalize`
  - Reports: GET `/api/matches/{match}/report`, GET `/api/reports/top-scorers`, GET `/api/reports/team-wins`

Full, importable Postman collection: `xyz_football_api.postman_collection.json`

## Postman

1) Import `xyz_football_api.postman_collection.json`
2) Set `{{base_url}}` (e.g., `http://127.0.0.1:8000`)
3) Call Auth - Login and set `{{token}}` from response
4) Use endpoints under Teams, Players, Matches, Results, Reports

## Notes

- Soft deletes are enabled for domain models; use restore endpoints where applicable.
- Results finalization is atomic; finished matches are immutable.
- File uploads (logos) are stored in `storage/app/public/logos` and served from `/storage/logos/...`.

## Troubleshooting

- **Rate limiter [api] is not defined**
  - This project defines the limiter in `App\\Providers\\AppServiceProvider::boot()`.
  - Ensure server is restarted after pulling changes.
  - Use a file cache locally to avoid DB cache errors:
    - In `.env`: `CACHE_STORE=file`, `SESSION_DRIVER=file`, `QUEUE_CONNECTION=sync`
    - Then run:
      ```
      php artisan config:clear
      php artisan route:clear
      php artisan view:clear
      php artisan cache:clear
      ```

---

