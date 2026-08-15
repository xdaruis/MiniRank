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
