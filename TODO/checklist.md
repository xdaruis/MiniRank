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

- [ ] **S4 Filter list** — OPTIONAL — filter by position range or movement; slots into list view after M4.
- [ ] **S1 Line chart** — OPTIONAL — hand-rolled inline SVG on detail page, no dependency.
- [ ] **S5 CSV export** — OPTIONAL — export a keyword's position history; endpoint off detail page.
- [ ] **S7 Docker** — OPTIONAL — `docker compose up` starts app + SQLite; complements M7.
- [ ] **S2 Multi-project/websites** — OPTIONAL — adds project entity; restructures M1/M2, largest optional.
- [ ] **S3 Accounts + CSRF** — OPTIONAL — hashed passwords, sessions, log in/out, CSRF on forms.
- [ ] **S6 PHPUnit** — OPTIONAL — `composer.json` + tests for seed bounds and trend logic.

## Quality + deliverables

- [ ] **M6 Security audit pass** — MAIN — verify every query is prepared, every output escaped, no secrets, all mutations POST. Do after each feature.
- [x] **process.html** — DELIVERABLE — exists with Plan/Prompts/Retrospective; needs final 3 prompts + hours.
- [ ] **Repo public + submission** — DELIVERABLE — push, make public at deadline, submit repo + session links by email.