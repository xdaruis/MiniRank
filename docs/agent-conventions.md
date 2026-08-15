# MiniRank — Agent Conventions

Stack, directory map, and coding style. Attach this file (or `@docs/agent-conventions.md`) before a coding prompt.

## Technologies

| Layer | Choice |
|---|---|
| Language | Plain PHP 8.x, no framework |
| Database | SQLite via PDO (prepared statements only) |
| AJAX | Vanilla JS `fetch()` |
| Frontend | HTML/CSS/JS, hand-written responsive CSS |
| Package manager | Composer (only for PHPUnit tests, optional) |

## Directory map

```
MiniRank/
├── public/                 # DOCUMENT ROOT — only thing web-exposed
│   ├── index.php           # front controller (routing)
│   ├── refresh.php         # M3 AJAX endpoint
│   ├── css/style.css
│   └── js/app.js
├── app/
│   ├── Controllers/        # KeywordController, PositionController
│   ├── Models/             # Database, Keyword, Position (ALL SQL here)
│   ├── Views/              # layout, keyword/list, keyword/form, keyword/detail
│   └── Core/               # Router, Request, Response (e()/view()/redirect)
├── database/               # schema.sql, seed.php, minirank.db (gitignored)
├── config/config.php       # DB path + site config (no secrets)
├── tests/                  # optional PHPUnit
├── README.md
├── process.html
├── AGENTS.md
├── GENERAL_RULES.md
├── opencode.json
└── .gitignore
```

## Rules

- ALL SQL lives in Models — never in Controllers, Views, or the router.
- Controllers are thin: parse request → call a Model → render a View.
- Views do output only; escape everything with the `Response::e()` helper.
- Query-param routing: `?route=keyword.edit&id=5` → `KeywordController::edit`.
- Web root is `public/` — run with `php -S localhost:8000 -t public`.
- Seed data is demo-only and idempotent.

## Coding style

- PSR-12 style: 4-space indent, `{ }` on own lines, lowercase snake_case for functions, `StudlyCase` for classes.
- One class per file, namespace matches directory (`App\Models\Keyword`).
- Always `declare(strict_types=1);` at the top of PHP files.
- Type-hint all parameters and return types.
- No `global`; inject dependencies via constructor.
- SQLite file lives at `database/minirank.db` and is gitignored.
