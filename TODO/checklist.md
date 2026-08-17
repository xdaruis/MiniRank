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
- [ ] **M7 README** — MAIN — setup, seed command, one-command `php -S localhost:8000 -t public`. Currently 1-line stub.

## Optional stretch (interleaved)

- [x] **S4 Filter list** — OPTIONAL — filter by position range or movement; slots into list view after M4.
- [x] **S1 Line chart** — OPTIONAL — hand-rolled inline SVG on detail page, no dependency.
- [x] **S5 CSV export** — OPTIONAL — export a keyword's position history; endpoint off detail page.
- [ ] **S7 Docker** — OPTIONAL — `docker compose up` starts app + SQLite; complements M7.
- [ ] **S2 Multi-project/websites** — OPTIONAL — adds project entity; restructures M1/M2, largest optional.
  - [ ] **S2.1** `schema.sql` — add `users`, `projects`, `keywords.project_id`, `UNIQUE(project_id, phrase)`, `UNIQUE(user_id, domain)`.
  - [ ] **S2.2** `app/Models/User.php` — `findByEmail`/`create`.
  - [ ] **S2.3** `app/Models/Project.php` — `userProjects`/`owns`/`firstFor`/`create`.
  - [ ] **S2.4** `app/Models/Keyword.php` — scope `all($userId,$projectId,$search)`, `idsForUser($userId,$projectId)`, `findOwned($userId,$id)`, project-scoped create/update/delete (+ownership guard).
  - [ ] **S2.5** `KeywordController` — `Auth::require()`, resolve project from `?project` (`Project::owns`, default `firstFor`), ownership guard on detail/export/edit/delete.
  - [ ] **S2.6** `PositionController::refresh` — project-scoped (`idsForUser($userId,$projectId)`), active project only.
  - [ ] **S2.7** `ProjectController` — add project GET/POST (+CSRF), redirect to `keyword.list&project=N`.
  - [ ] **S2.8** Views — `list.php` project switcher, `form.php` hidden project, `detail.php` project back-link, `project/form.php`.
  - [ ] **S2.9** `seed.php` — migration guard (rebuild stale `keywords`/`projects`/`users`), demo user + project, 5 keywords under it, print demo creds.
- [ ] **S3 Accounts + CSRF** — OPTIONAL — hashed passwords, sessions, log in/out, CSRF on forms.
  - [ ] **S3.1** `app/Core/Auth.php` — session init (HttpOnly + SameSite), `login`/`logout`/`userId`/`user`/`require`, `session_regenerate_id` on login.
  - [ ] **S3.2** `app/Core/Csrf.php` — `token`/`field`/`verify` (`hash_equals`).
  - [ ] **S3.3** `AuthController` — `login` GET/POST, `logout` POST, `register` GET/POST (all POSTs +CSRF, `password_hash`).
  - [ ] **S3.4** `app/Views/auth/login.php` + `auth/register.php`.
  - [ ] **S3.5** `Router.php` — add `auth.login/logout/register`; protect app routes via `Auth::require()`.
  - [ ] **S3.6** `layout.php` — conditional nav (login link vs user email + Logout POST, CSRF `<meta>`).
  - [ ] **S3.7** `app.js` — send CSRF token + project in refresh POST body.
- [ ] **S6 PHPUnit** — OPTIONAL — `composer.json` + tests for seed bounds and trend logic.

## Quality + deliverables

- [ ] **M6 Security audit pass** — MAIN — verify every query is prepared, every output escaped, no secrets, all mutations POST. Do after each feature.
- [x] **process.html** — DELIVERABLE — exists with Plan/Prompts/Retrospective; needs final 3 prompts + hours.
- [ ] **Repo public + submission** — DELIVERABLE — push, make public at deadline, submit repo + session links by email.