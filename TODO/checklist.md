# MiniRank — Implementation Checklist

Ordered top to bottom. `[ ]` unsolved, `[x]` solved. Optional tasks interleaved where logical.

## Foundation

- [x] **Skeleton + git init** — MAIN — MVC folders, PDO factory, Router/Request/Response, schema, `.gitignore`. Done.
- [x] **S8 `AGENTS.md`** — OPTIONAL — hand-written conventions + `docs/agent-conventions.md`. Done.

## Must-haves (core product)

- [x] **M2 Seed script** — MAIN — `database/seed.php`: ~5 keywords, ~30 days random-walk positions 1–100, idempotent. Currently stub.
- [x] **M1 Keywords CRUD** — MAIN — `Keyword::create/update/delete/find/all` prepared statements; add/edit POST forms, delete POST.
- [x] **M4 List + trend + search** — MAIN — `Keyword::all($search)` + `Position::current/trend` (7-day: improved=▲/declined=▼/stable==).
- [x] **M5 Detail page** — MAIN — `Position::history()` table, newest first, 404 on missing keyword.
- [x] **M3 Refresh via AJAX** — MAIN — `Position::refreshForToday()` upsert + `PositionController::refresh` JSON + `app.js` fetch updates cells only. Controller currently stub.
- [x] **M8 Responsive** — MAIN — verify/complete `style.css` media queries at phone width.
- [x] **M7 README** — MAIN — requirements (PHP ≥8.2 + Composer), macOS/Homebrew + Ubuntu/apt install, `composer install`/`.env`/seed/run/`composer test` commands. Done.

## Optional stretch (interleaved)

- [x] **S4 Filter list** — OPTIONAL — filter by position range or movement; slots into list view after M4.
- [x] **S1 Line chart** — OPTIONAL — hand-rolled inline SVG on detail page, no dependency.
- [x] **S5 CSV export** — OPTIONAL — export a keyword's position history; endpoint off detail page.
- [ ] **S7 Docker** — OPTIONAL — `docker compose up` starts app + SQLite; complements M7.
- [x] **S2 Multi-project/websites** — OPTIONAL — adds project entity; restructures M1/M2, largest optional. Done.
  - [x] **S2.1** `schema.sql` — add `users`, `projects`, `keywords.project_id`, `UNIQUE(project_id, phrase)`, `UNIQUE(user_id, domain)`.
  - [x] **S2.2** `app/Models/User.php` — username-based `findByUsername`/`create` (done in S3; email variant not used).
  - [x] **S2.3** `app/Models/Project.php` — `userProjects`/`owns`/`firstFor`/`create`.
  - [x] **S2.4** `app/Models/Keyword.php` — scope `all($userId,$projectId,$search)`, `idsForUser($userId,$projectId)`, `findOwned($userId,$id)`, project-scoped `create(projectId,$phrase)` (+ownership guard on edit/delete).
  - [x] **S2.5** `KeywordController` — resolve project from `?project` (`Project::owns`, default `firstFor`, none→"add project" prompt), ownership guard on detail/export/edit/delete.
  - [x] **S2.6** `PositionController::refresh` — project-scoped (`idsForUser($userId,$projectId)`), ownership 404.
  - [x] **S2.7** `ProjectController` — add project GET/POST (+CSRF), redirects to `keyword.list&project=N`.
  - [x] **S2.8** Views — `list.php` project switcher + `data-project`, `form.php` hidden project, `detail.php` project back-link/export, `project/form.php`, `project/none.php`.
  - [x] **S2.9** `seed.php` — FK-order rebuild (positions/keywords/projects/users + sqlite_sequence), demo user + project + 5 keywords under it, prints demo creds.
- [x] **S3 Accounts + CSRF** — OPTIONAL — hashed passwords, sessions, log in/out, CSRF on forms. Done.
  - [x] **S3.1** `app/Core/Auth.php` — session init (HttpOnly + SameSite), `login`/`logout`/`userId`/`user`/`require`, `session_regenerate_id` on login.
  - [x] **S3.2** `app/Core/Csrf.php` — `token`/`field`/`verify` (`hash_equals`).
  - [x] **S3.3** `AuthController` — `login` GET/POST, `logout` POST, `register` GET/POST (all POSTs +CSRF, `password_hash`, min 8-char password).
  - [x] **S3.4** `app/Views/auth/login.php` + `auth/register.php` (card layout, cross links).
  - [x] **S3.5** `Router.php` — add `auth.login/logout/register`; protect app routes via `Auth::require()`.
  - [x] **S3.6** `layout.php` — conditional nav (login link vs username + Logout POST, CSRF `<meta>`).
  - [x] **S3.7** `app.js` — send CSRF token in refresh POST body; refresh.php hardened (direct controller call, JSON 401, 403 on bad CSRF).
- [x] **S6 PHPUnit** — OPTIONAL — `composer.json` (`phpunit ^11`) + `phpunit.xml` + `tests/` split into `Models/`/`Core/`/`Http/` + `Support/` (autoload-dev, `.env` `DATABASE_PATH` override). Covers security invariants (ownership isolation, CSRF, POST-only mutations), auth flows (login/register/logout), list filters, trend/refresh logic, model edges. 65 tests. Done.

## Nice-to-have (OPTIONAL — not required by reviewers)

Only addressed if requested feature work continues. No acceptance impact.
These are not part of the submission scope.

### Features

- [ ] **Pagination** — OPTIONAL — `page`/`per_page` on keyword list + position history; index on `positions(keyword_id, captured_at)` already present.

### Bugs (from code-smell audit)

- [ ] **Literal search** — escape `%`/`_` in `Keyword::all` pattern so searches are literal, not SQL-wildcard.
- [ ] **Single source of truth for trend** — consolidate trend glyph/label rendering (currently duplicated in `list.php` backend + `app.js` frontend).
- [ ] **Refresh error feedback** — surface 403/404/405 to the user instead of silently swallowing (current `app.js` `catch`).
- [ ] **List N+1** — compute trend once per keyword per render (currently queried in filter + again in view).
- [ ] **`.env` parser hardening** — strip inline `#` comments/quotes; clarify real-`getenv()` precedence over `.env`.
- [ ] **Multibyte password length** — `mb_strlen` in register validation (current `strlen` counts bytes).
- [ ] **Per-request `Auth::user()`** — resolve the user once per request instead of re-querying on each `layout` render.

## Quality + deliverables

- [x] **M6 Security audit pass** — MAIN — verify every query is prepared, every output escaped, no secrets, all mutations POST. Coveverification encoded in AGENTS.md; live probes passed (SQLi, CSRF, GET-mutation, authz 404, XSS escape). Hardening: `findOwned` project-scoped on edit/detail/export/delete; batch refresh transactional; trend fallback bounded to 7-day window.
- [x] **process.html** — DELIVERABLE — exists with Plan/Prompts/Retrospective; needs final 3 prompts + hours.
- [ ] **Repo public + submission** — DELIVERABLE — push, make public at deadline, submit repo + session links by email.