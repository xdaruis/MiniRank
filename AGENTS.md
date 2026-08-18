# AGENTS.md — MiniRank project instructions

Auto-loaded every session. Read this first, then read `docs/agent-conventions.md` before a coding prompt.

## Project

MiniRank is a single-user PHP keyword position tracker (simulated data). Plain PHP 8.x, no framework. SQLite via PDO. Vanilla JS for AJAX.

## Voice — chat replies only, NOT files

- TELEGRAPHIC. Substance only. Zero fluff. Banned words: the, a, an, I, me, my, you, your, basically, actually, sure, certainly, hello, hope, apologies.
- Sentence shape: Object. Action. Result.
- Tone: clinical. Audience: expert. No lectures, self-references, hedging, pleasantries, transitions.

## Modes — declare `[MODE: NAME]` at start of reply

- RESEARCH — facts. No code.
- INNOVATE — brainstorm. Possibilities + pros/cons.
- PLAN — atomic checklist. No creative choices during EXECUTE.
- EXECUTE — implement plan 100%. No deviation.
- REVIEW — `Match: Yes/No` + `⚠ Deviation: [info]`.

## Code rules

- New code: complete and self-contained. Edits: minimal context, highlight changes.
- Max code block: 200 lines.
- Never write unrequested code. Follow the plan. No warm-up, no "I will help you".

## Non-negotiables

- Every SQL query is a PDO prepared statement with bound parameters — never string-concatenated values.
- Every output value passes through output escaping (`htmlspecialchars`).
- No secrets committed to the repository.
- Mutations (add/edit/delete/refresh) are POST, never GET.
- Small commits, meaningful messages, no squash, no force-push.

## Security audit (M6) — run after every feature

Verify each, fix any failure before committing:

1. Prepared statements — all CRUD via `->prepare() + execute([...])`; SQL lives only in `app/Models/`. Concatenated only allowed for whitelisted (enum) values.
   `rg -n 'query\(|exec\(|prepare\(' app/Models app/Core app/Controllers`
   `rg -n '\$sql\s*\.=|\.\s*\$_' app/Models`
   `rg -n '\$_GET|\$_POST' app/Models`
2. Escaped output — every DB/request-derived value echoed through `Response::e()`. Static literals + int casts exempt.
   `rg -n 'echo |<\?=' app/Views --glob '*.php' | rg -v 'Response::e|content\(\)'`
3. No secrets — no credentials in `config/`; db/env/vendor gitignored.
   `git ls-files | rg -i '\.db$|\.env$|secret|credential|password'`
   `git check-ignore database/minirank.db`
4. Mutations POST + CSRF — every mutating handler guards `isPost()` then `Csrf::verify`. New POST forms include `Csrf::field()`; JSON/POST handlers read `csrf_token`.
   `rg -n 'isPost\(\)|Csrf::verify' app/Controllers`
5. Ownership isolation — multi-user scope: any record read/write cross-user must fail (keyword/edit/delete/refresh/detail return 404 or no-op). Verify live per feature.

Live probe checklist (built-in server + curl): SQLi search payload returns 0 rows; POST mutation without `csrf_token` inserts nothing; GET to a delete route removes nothing; cross-account record access 404; reflector username renders escaped (`&lt;`), never raw.
