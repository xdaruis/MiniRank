# MiniRank

A multi-user PHP keyword position tracker with per-project keyword sets and simulated rank data. Plain PHP 8.x, no framework. SQLite via PDO, session auth with hashed passwords + CSRF, vanilla JS for AJAX.

## Requirements

- [PHP](https://www.php.net/) >= 8.2 with `pdo_sqlite` (`sqlite3` / `pdo_sqlite` / `mbstring` extensions)
- [Composer](https://getcomposer.org/)

## Install

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

## Setup (from the repo root)

```sh
composer install          # installs dependencies incl. PHPUnit
cp .env.example .env      # optional — override DATABASE_PATH
php database/seed.php     # demo user, 5 keywords, 30 days of positions
```

The seed creates the demo account `demo` / `password` under project `pizzeria.example`.

## Run

```sh
php -S localhost:8000 -t public
```

Open http://localhost:8000 and log in with `demo` / `password`.

## Tests

```sh
composer test             # runs vendor/bin/phpunit
```

Tests use a throwaway temp database; they never touch `database/minirank.db`.

## Configuration

Copy `.env.example` to `.env` to override settings. `DATABASE_PATH` is resolved relative to the project root (default `database/minirank.db`). The real `.env` is gitignored; no secrets are committed to the repository.