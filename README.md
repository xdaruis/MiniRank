# MiniRank

A multi-user PHP keyword position tracker with per-project keyword sets and simulated rank data. Plain PHP 8.x, no framework. SQLite via PDO, session auth with hashed passwords + CSRF, vanilla JS for AJAX.

## Two ways to run

| | Self-run | Docker |
|---|---|---|
| Needs locally | PHP ≥ 8.2 + extensions, Composer (tests only) | Docker Engine + Compose only |
| App | `php -S localhost:8000 -t public` | `docker compose up --build` |
| Tests | `composer test` | `docker compose -f docker-compose.test.yml run --rm test` |
| Prod | not bundled — requires a web server | `docker compose -f docker-compose.prod.yml up --build` |

Pick the self-run path if PHP is already installed, or Docker to avoid installing anything.

---

## Option A — Self-run (native PHP)

### Requirements

- [PHP](https://www.php.net/) >= 8.2 with `pdo_sqlite` (`sqlite3` / `pdo_sqlite` / `mbstring` extensions)
- [Composer](https://getcomposer.org/) — required only to run the test suite

**Troubleshooting note:** the PHP/Composer install commands below reflect the current official packages for each OS. If any command fails (renamed package, changed installer, PATH issue), consult the official documentation for PHP (`php.net/install`), Composer (`getcomposer.org/download`), or your package manager before continuing — the versions move faster than this README.

### macOS (Homebrew)

```sh
brew install php composer
php -v
php -m | grep -i sqlite   # expect: pdo_sqlite, sqlite3, mbstring
composer --version
```

### Ubuntu / Debian (apt)

```sh
sudo apt update
sudo apt install php-cli php-sqlite3 php-mbstring unzip
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
composer --version
php -m | grep -i sqlite
```

PHPUnit is a dev dependency of this project — `composer install` fetches it. No separate PHPUnit install is needed.

### Windows (XAMPP or winget)

- XAMPP (recommended) bundles PHP 8.2+ with `pdo_sqlite`, `sqlite3`, `mbstring`. Install from apachefriends.org; or `winget install PHP.PHP` for a bare binary.
- Ensure `php.exe` is on PATH — open a fresh terminal and run `php -v`.
- `php -m | findstr sqlite`  → expect `pdo_sqlite`, `sqlite3`, `mbstring`.
- Composer: `winget install Composer.Composer` (tests only).

### Setup (from the repo root)

```sh
composer install          # only needed for tests
cp .env.example .env      # optional — override DATABASE_PATH
php database/seed.php     # demo user, 5 keywords, 30 days of positions
```

The seed creates the demo account `demo` / `password` under project `pizzeria.example`.

### Run

```sh
php -S localhost:8000 -t public
```

Open http://localhost:8000 and log in with `demo` / `password`.

### Tests

```sh
composer test             # runs vendor/bin/phpunit
```

Tests use a throwaway temp database; they never touch `database/minirank.db`.

---

## Option B — Docker

Zero local dependency setup: the images bundle PHP 8.2 with `pdo_sqlite`, `sqlite3`, and `mbstring`. No PHP and no Composer are installed on your machine.

Prerequisites: [Docker Engine](https://docs.docker.com/engine/install/) with the [Compose plugin](https://docs.docker.com/compose/install/). Verify:

```sh
docker --version
docker compose version
```

### Development

```sh
docker compose up --build
```

Opens on http://localhost:8000. The source is bind-mounted, so edits reload immediately. On first boot with no database, the demo data is seeded automatically. The SQLite file lives on a named volume and survives restarts.

### Production

```sh
docker compose -f docker-compose.prod.yml up --build
```

Opens on http://localhost:8081 behind nginx + php-fpm. A one-shot `seed` service populates the shared volumes on the first boot; the database persists across `down`/`up`.

### Tests

```sh
docker compose -f docker-compose.test.yml run --rm test
```

A Composer-enabled image runs `composer install` (into the bind-mounted `vendor/`) and then `vendor/bin/phpunit`. Test runs are reproducible and isolated from the running app containers.

---

## Demo login (both paths)

`demo` / `password` (project: `pizzeria.example`).

The seed runs only when no `minirank.db` exists, so existing data is never wiped on restart.

## Configuration

Self-run: copy `.env.example` to `.env` to override settings. `DATABASE_PATH` is resolved relative to the project root (default `database/minirank.db`).

Docker: the compose files already set `DATABASE_PATH` to the container's data volume. The real `.env` is gitignored; no secrets are committed to the repository.