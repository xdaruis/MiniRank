# MiniRank — Implementation Plan

Build plan for the full-stack PHP keyword position tracker. Written before coding starts, per the assessment's "plan first" guidance.

## 1. Locked decisions

| Decision | Choice | Why |
|---|---|---|
| Backend | Plain PHP 8.x, no framework | Smallest context footprint for free models; security visible; brief warns against big scaffolds |
| Database | SQLite via PDO | One file, `php -S` just works, zero setup for reviewers |
| DB access | PDO prepared statements | M6: no string-concatenated SQL |
| AJAX | Vanilla JS `fetch()` | One button; no library needed |
| Package manager | Composer installed, used only for PHPUnit (S6) | Core app stays dependency-free |
| CSS | Hand-written, responsive media queries | M8 |
| Routing | Query params `?route=list&id=5` | Dead simple, works on `php -S` |
| Website config | Hardcode one site in `config.php` | M1 = single configured website |
| Seed pattern | Random walk (positions drift up/down) | Natural-looking trends; M2 needs ~30 days 1–100 |
| Sessions | `$_SESSION` available but unused for must-haves | No login needed (M1); kept for S3 later |

## 2. Project structure (lightweight MVC)

Plain PHP, but organized by role: models (data), views (output), controllers (routing/logic). A single `public/` web root keeps PHP files out of the browser URL — only `public/` is web-exposed; everything else lives above it.

```
MiniRank/
├── public/                          # DOCUMENT ROOT — only thing web-exposed
│   ├── index.php                    # front controller (routes to controllers)
│   ├── refresh.php                  # M3 AJAX endpoint (thin -> controller)
│   ├── css/
│   │   └── style.css                # responsive stylesheet
│   └── js/
│       └── app.js                   # fetch() for refresh + search
│
├── app/
│   ├── Controllers/
│   │   ├── KeywordController.php    # list/add/edit/delete handlers
│   │   └── PositionController.php   # refresh + trend logic handler
│   ├── Models/
│   │   ├── Database.php             # PDO connection factory (SQLite)
│   │   ├── Keyword.php              # keyword CRUD queries (prepared)
│   │   └── Position.php             # position history + trend queries
│   ├── Views/
│   │   ├── layout.php               # HTML shell, shared header/footer
│   │   ├── keyword/list.php         # M4 keyword list + search + trend
│   │   ├── keyword/form.php         # M1 add/edit keyword form
│   │   └── keyword/detail.php       # M5 position history table
│   └── Core/
│       ├── Router.php               # maps ?route=... to controller/action
│       ├── Request.php              # $_GET/$_POST/$_SERVER wrapper
│       └── Response.php             # e() escape helper, view() renderer, redirect
│
├── database/
│   ├── schema.sql                   # table definitions
│   ├── seed.php                     # M2 CLI seed script
│   └── minirank.db                  # SQLite file (gitignored, created at runtime)
│
├── config/
│   └── config.php                   # DB path + site config (no secrets)
│
├── tests/                           # S6 PHPUnit (optional)
│   └── TrendTest.php
│
├── README.md                        # M7 setup + seed + one-command start
├── process.html                     # deliverable 4 (process document)
├── AGENTS.md                        # S8 hand-written conventions
├── composer.json                    # PHPUnit only (S6), optional
└── .gitignore                       # excludes database/*.db, vendor/, .DS_Store
```

### How it hangs together

- **Web root is `public/`** — reviewers run `php -S localhost:8000 -t public`. Nothing sensitive (config, schema, seed, controllers) is reachable by URL.
- **`index.php`** builds a `Router` and calls the matching controller action (e.g. `?route=keyword.edit&id=5` → `KeywordController::edit`).
- **Controllers** are thin: parse the request, call a Model, pass data to a View via `Response::view()`.
- **Models** own all SQL — every query is a PDO prepared statement (M6).
- **`refresh.php`** is a 3-line endpoint delegating to `PositionController::refresh`, which returns JSON for `app.js`.

### Structure trade-off vs flat

| | Flat (old) | MVC folders (new) |
|---|---|---|
| Web-exposed files | everything | only `public/` |
| Where SQL lives | mixed | only Models |
| Where routing lives | ad hoc | Router + Controllers |
| Where HTML lives | mixed | only Views |
| Cost | none | a few small Core classes, ~200 lines |

Keeps the small context footprint for free models, but gives reviewers a clear "where is X" answer. This satisfies the brief's "explain your structure choice" and reads well in the defense.

## 3. Schema (schema.sql)

```sql
-- One configured website (single-user). Website metadata lives in config.php.

CREATE TABLE IF NOT EXISTS keywords (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    phrase      TEXT NOT NULL UNIQUE,
    created_at  TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS positions (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    keyword_id  INTEGER NOT NULL REFERENCES keywords(id) ON DELETE CASCADE,
    position    INTEGER NOT NULL CHECK (position BETWEEN 1 AND 100),
    captured_at TEXT NOT NULL,          -- date (YYYY-MM-DD)
    UNIQUE (keyword_id, captured_at)    -- one reading per keyword per day
);

CREATE INDEX IF NOT EXISTS idx_positions_keyword_date
    ON positions (keyword_id, captured_at);
```

Design notes:
- `ON DELETE CASCADE` so deleting a keyword (M1) removes its history.
- `UNIQUE (keyword_id, captured_at)` guarantees a single reading per day — makes refresh idempotent (refresh twice = update, not duplicate).
- Position `CHECK` 1–100 matches M2.

## 4. Build order (one session/commit per step)

Order rationale: seed before CRUD so the list has data; refresh last because it ties server + JS together. New opencode session per step, small commit each.

### Step 0 — Day-0 skeleton + git init
- `git init`, `.gitignore` (`database/*.db`, `vendor/`, `.DS_Store`).
- Scaffold the MVC folders: `public/`, `app/Controllers`, `app/Models`, `app/Views`, `app/Core`, `database/`, `config/`.
- `database/schema.sql`, `config/config.php`, `app/Models/Database.php` (PDO factory), Core classes (`Router`, `Request`, `Response`), and a stub `public/index.php` returning a placeholder page.
- Verify: `php -S localhost:8000 -t public` serves a page; a tiny script or the seed later runs `schema.sql`.
- Commit: `init: MVC skeleton, schema, PDO connection`.

### Step 1 — M2 seed script
- `database/seed.php` CLI: creates ~5 demo keywords, then for each generates ~30 days of daily positions via random walk (position 1–100), plus a row for "today" so list/detail always have current data.
- Make it idempotent: `DELETE FROM keywords;` then reseed (safe — demo data only). Uses the Models, not raw SQL.
- Verify: run `php database/seed.php`, then inspect the SQLite file (`.tables`, counts).
- Commit: `feat: seed script with 30 days of simulated positions`.

### Step 2 — M1 keywords CRUD
- `app/Core/Router.php` routes: `?route=keyword.list` (default), `?route=keyword.add`, `?route=keyword.edit&id=N`, `?route=keyword.delete&id=N`.
- `app/Controllers/KeywordController.php` + `app/Views/keyword/form.php`: add/edit are POST forms; `app/Models/Keyword.php` handles insert/update/delete via PDO prepared statements with bound params.
- Delete is POST (avoid GET-mutation); confirm on the client.
- Output: all values through `Response::e()` → `htmlspecialchars()` (M6).
- Verify: add, edit, delete a keyword; confirm history cascades on delete.
- Commit: `feat: keyword add/edit/delete (prepared statements)`.

### Step 3 — M4 keyword list + trend + search
- List table columns: phrase | current position | 7-day trend | actions.
- **Current position**: `app/Models/Position.php::current()` (latest row per keyword).
- **7-day trend**: `app/Models/Position.php::trend()` — compare position 7 days ago vs today's. Improved = position went *down* (1 is best); declined = went *up*; equal = stable. Show ▲ / ▼ / =.
- **Text search**: `?route=keyword.list&q=...` → `WHERE phrase LIKE ?` with bound `%q%`.
- Verify: search filters; each keyword shows a sensible trend arrow.
- Commit: `feat: keyword list with current position, trend, search`.

### Step 4 — M5 keyword detail page
- `?route=keyword.detail&id=N` → `app/Views/keyword/detail.php` history table: date | position, newest first.
- 404 / friendly message if the keyword doesn't exist.
- Verify: clicking a keyword from the list opens its full history.
- Commit: `feat: keyword detail page with position history table`.

### Step 5 — M3 refresh positions (server + AJAX)
- `public/refresh.php`: thin POST endpoint → `PositionController::refresh`. For today (or each keyword without today's reading), generate a new position server-side via `Position::refresh()`, `INSERT ... ON CONFLICT(keyword_id, captured_at) DO UPDATE`. Return JSON `[{keyword_id, position, trend}]`.
- `public/js/app.js`: on button click → `fetch('refresh.php', {method:'POST'})` → update only the position + trend cells in the DOM. No page reload.
- Verify: click refresh → position/trend cells update in place, page doesn't reload (watch Network tab / no full render).
- Commit: `feat: refresh positions via AJAX`.

### Step 6 — M7 README + M8 responsive polish
- `README.md`: prerequisites, `git clone`, `php database/seed.php`, `php -S localhost:8000 -t public`, one-command start. Point to SQLite = no DB server; note `public/` is the web root.
- Responsive: mobile-first CSS, media query for >768px to lay the table in columns; allow horizontal scroll on small tables.
- Verify: open on a narrow window / devtools device mode — usable.
- Commit: `docs: README + responsive styling`.

### Step 7 — Stretch goals (only after must-haves work, time permitting)
- **S1 line chart** on detail page — hand-rolled inline SVG (no dependency) plotting position over time.
- **S6 PHPUnit** — `composer require --dev phpunit/phpunit`; test trend calculation + seed bounds.
- **S8 AGENTS.md** — hand-write conventions the agent must follow.
- **S3 sessions/accounts** — only if everything else is solid; adds login, hashing, CSRF.

### Step 8 — Process document + submission
- Write `process.html` with the agent: Plan / Prompts & fixes / Retrospective (3 real prompts, one failed, hours spent).
- Share substantial opencode sessions (`/share`) and collect links into process.html.
- Make the repo public at deadline, reply to the email with repo + session links.

## 5. Security checklist (M6) — verify each step

- [ ] Every SQL uses PDO prepared statements / bound params — no concatenated values.
- [ ] Every echoed variable passed through `htmlspecialchars()`.
- [ ] No secrets in repo — `.gitignore` excludes the `.db` file and any config that would hold credentials (SQLite = none by design).
- [ ] Mutations (add/edit/delete/refresh) are POST, not GET.
- [ ] (S3 later) CSRF tokens on forms if accounts are added.

## 6. Free-model working rules (from setup guide)

- Plan per feature, then switch to Build.
- New opencode session per step; share before moving on.
- On 429: wait, or drop to Gemini → OpenRouter fallback.
- Read the Review panel diff before building on top of it.
- Run the app after every step.

## 7. Time budget (target ~6–10h)

| Step | Est. time |
|---|---|
| 0 skeleton + git | 45 min |
| 1 seed | 45 min |
| 2 CRUD | 1.5 h |
| 3 list/trend/search | 1.5 h |
| 4 detail | 1 h |
| 5 refresh AJAX | 1.5 h |
| 6 README + responsive | 45 min |
| 7 stretch (optional) | as budget allows |
| 8 process doc + submission | 1 h |
