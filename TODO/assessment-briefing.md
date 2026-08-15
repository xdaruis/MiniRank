# MiniRank — Full Assessment Briefing

Complete consolidation of the assessment brief and setup guide. Use this as your single planning reference for the rankingCoach AI coding assessment.

## 1. Overview

- **What:** Build a small full-stack PHP app — **MiniRank**, a keyword position tracker (simulated data, no real search engines).
- **Why:** Measures your ability to **plan, instruct, supervise and verify** an AI agent — not how much PHP you have memorized. Many candidates come from Java/JS; being productive in a new stack via an agent is exactly the point.
- **How:** Use the **opencode desktop app** with **free AI models** following the setup guide.
- **Window:** 14.08 – 18.08.2026, ~6–10h of actual effort.
- **Deadline:** Tuesday, 18.08.2026, 23:59.

## 2. Timeline

| When | What |
|---|---|
| Friday, 14.08.2026 | Test window opens — start as soon as your pre-flight passes. |
| By Saturday, 15.08.2026, end of day | **Day-0 pre-flight** (see section 5): opencode installed, model authenticated, test prompt answered, confirmation sent. Solve setup problems *now*, not during the test window. |
| Tuesday, 18.08.2026, 23:59 | Deadline: repository public, link + session links submitted. |
| 19.08 – 21.08.2026 | **Defense** (shortlisted, invited individually): ~20 min. They run your app, pick a piece of code and ask you to explain it, then ask for one small change made live with the agent. |

## 3. Must-haves (all required)

| # | Requirement | Detail |
|---|---|---|
| M1 | **Keywords CRUD** | Add / edit / delete keyword (search phrase) tracked for ONE configured website. Single-user app, **no login**. |
| M2 | **Seeded history** | A seed script creates demo keywords with ~30 days of daily positions (range 1–100). |
| M3 | **Refresh simulation** | A "Refresh positions" button generates today's positions server-side and updates the page **via AJAX, without a full page reload**. |
| M4 | **Keyword list** | Every keyword with: current position, a 7-day trend indicator (improved / declined / stable), and a text search. |
| M5 | **Keyword detail** | A page per keyword showing the position history as a table. |
| M6 | **Security basics** | Parameterized queries (NO SQL built by string concatenation), escaped output, and **no secrets committed to the repository**. **Heaviest-quality item — 25% of score.** |
| M7 | **Runs in 5 minutes** | README with setup steps, a seed command, and a one-command start (`php -S ...` or `docker compose up`). Database: **SQLite (recommended — easiest to run)** or MySQL. |
| M8 | **Responsive** | Usable at phone width. Functional beats beautiful — no pixel-perfection grading. |

## 4. Stretch goals (optional — attempt only after must-haves work)

Where stronger candidates differentiate.

| # | Idea |
|---|---|
| S1 | Line chart on the keyword detail page (any JS library, or hand-rolled SVG/canvas). |
| S2 | Multiple projects/websites, each with its own keywords. |
| S3 | User accounts — register / log in / log out (hashed passwords, sessions) — plus **CSRF protection on forms**. |
| S4 | Filter keywords by position range or by movement (improved/declined). |
| S5 | CSV export of a keyword's position history. |
| S6 | PHPUnit tests for core logic (seeding, trend calculation). |
| S7 | Docker setup: `docker compose up` starts app + database. |
| S8 | An `AGENTS.md` with real, hand-written project conventions (graded for quality if present). |

## 5. Technology rules

- **Backend:** PHP — any style: plain PHP, micro-framework (e.g. Slim), or full framework. Tip: free models have smaller context windows; a large framework scaffold can work against you. Choose deliberately and **explain the choice in your write-up**.
- **Frontend:** HTML/CSS/JS — any libraries you like.
- **AI:** All AI-assisted coding must go through the **opencode desktop app** with the model config from the setup guide. You may use chat AIs (ChatGPT, etc.) for brainstorming, but **disclose it** in your process document.
- **Your own work.** Public tutorials, docs and Composer packages are fine; code from other candidates is not.

## 6. Day-0 pre-flight (mandatory — by Saturday, 15.08.2026, end of day)

1. Create an empty folder and open it as a project in the opencode app.
2. Confirm your PRIMARY model is selected in the model picker.
3. Ask the agent: *"Create hello.php that prints the current date, then explain what you did."* — confirm the file appears in the file tree, then run it yourself: `php hello.php`.
4. Share the session — type `/share` (or session menu ⋯ → Share…); the link lands in your clipboard.
5. Reply to the email you received this test in with: that link + which models you have configured (PRIMARY + at least one fallback).

If anything fails, ask **now** — setup problems during the test window cost you your own time.

## 7. Model configuration (setup guide)

Use this order. Build with PRIMARY; move down the chain only when rate-limited.

| Priority | Provider | Model | Notes |
|---|---|---|---|
| PRIMARY | opencode Zen (built into app) | **DeepSeek V4 Flash Free** — if missing from the list, use another **Free**-badged Zen model (e.g. MiMo V2.5 Free, Nemotron 3 Ultra Free) and mention the switch in your process doc | The free list rotates |
| FALLBACK 1 | Google Gemini (API key) | free-tier **Flash** model available to your AI Studio key | Free API key via AI Studio, no card |
| FALLBACK 2 | OpenRouter | any model with the `:free` suffix | Hard caps: 20 req/min, **50 req/day** — emergency lane only |

**Setup essentials:**
- Create your free opencode account first at [opencode.ai/auth](https://opencode.ai/auth) — **Continue with GitHub** (same account you'll use for the test repo). No credit card, no billing.
- Pick the model in the model picker (search "free"; free models carry a **Free** badge).
- Bookmark your opencode workspace — the **Usage** tab shows every request with token counts. A single trivial prompt already carries ~10k input tokens of context — keep sessions small.
- Free Zen models are free because they're in promo/trial periods; prompts may be used for model training. Don't paste anything personal.

**Fallback setup:** Gemini → get a free API key at [aistudio.google.com](https://aistudio.google.com); add the provider and paste the key. OpenRouter → free account at [openrouter.ai](https://openrouter.ai), generate a key, add provider, select a `:free` model (know the caps: 20 req/min, 50 req/day).

## 8. Working within free limits (read twice)

- **Plan first.** Agree the architecture in Plan mode before generating code. One good plan saves thirty bad requests.
- **Small steps.** "Implement the keyword CRUD (routes, queries, views) for the schema in schema.sql" beats "build the whole app." Giant prompts produce giant diffs, giant reviews, and burned quota.
- **New session per feature.** Long sessions overflow small context windows; the model starts forgetting earlier decisions. Share the old session before moving on.
- **Rate-limited (429)?** Wait a minute or two, or switch to the next model in the chain. Note it in your process doc — handling limits is part of the assessment.
- **Verify as you go.** Run the app after every step. Free models produce plausible-looking code that is wrong — or insecure — more often than paid ones. Your review is the quality bar.
- **Budget across days.** Daily quotas usually reset every 24h; don't leave the must-haves for the last evening.

## 9. opencode features that matter

All sit in or under the prompt box.

| Feature | Where | Why you care |
|---|---|---|
| **Plan / Build** | first dropdown under the prompt box | Plan discusses without touching files; Build edits them. **Plan first**, then switch to Build. |
| **Model picker** | the model name under the prompt box | Your move when rate-limited: switch to the next model in the chain. Search "free" to see your free models. |
| **Effort selector** | last dropdown under the prompt box | Leave on **Default** — higher settings think longer and spend your quota faster. |
| **Share session** | type `/share`, or session menu (⋯) → Share… | Creates the public session link (copied to clipboard) — required for your submission. Unshare exists if you shared the wrong session. |
| **Review panel** | right side of the window | Shows every change as a git diff. **Read the diff before you build on top of it.** Blindly accepted diffs are how SQL injection gets in. |
| **New session per feature** | the **+** next to the session tabs | Keeps context small; long sessions overflow free-model context windows. Share the old session before moving on. |
| **AGENTS.md** | type `/init` — "guided AGENTS.md setup" | The instruction file the agent reads. Generate it, then improve it by hand with your conventions. |
| **`/` and `@`** | in the prompt box | `/` lists every command (`/undo`, `/redo`, `/review`, …); `@` attaches files as context to your prompt. |

Full reference: [opencode.ai/docs](https://opencode.ai/docs/) — the app evolves quickly, trust the app over screenshots.

## 10. Troubleshooting

| Symptom | Fix |
|---|---|
| 429 / rate-limit errors | Wait briefly, then use the fallback chain |
| Empty or cut-off answers | Context overflow → new session, smaller prompt |
| Provider auth fails | Re-add the provider; check the key was copied fully |
| Agent can't find `php` (Windows) | Open a fresh terminal and check `php -v`; if it fails, PHP isn't on your PATH — re-run the XAMPP/winget install, then restart the opencode app |
| App misbehaves or won't start | Update to latest from [opencode.ai/download](https://opencode.ai/download) and retry; if still broken, reply to the test email **immediately** — don't lose a day to a tooling problem |
| Shared link doesn't open | Re-share the session; if still broken, mention it in your submission and we'll take the exported session instead |

## 11. Deliverables (submit these)

1. **GitHub repository** — created **private**, made **public at the deadline**, link submitted by replying to the email you received this brief in.
2. **Real commit history** — `git init` at the start, commit as you go, small commits with meaningful messages. **No squashing, no force-push, no single "final version" commit.** The history is part of the assessment.
3. **Session links** — share your substantial opencode sessions (`/share`, or the session menu → Share) and list the links in your process document. We will read your actual prompts, including the ones that failed. That is a good thing: recovering from a bad prompt is skill, not shame.
4. **process.html** — your process document, built with the agent, with three sections:
   - **Plan** — your plan and key decisions, and what you would change in hindsight;
   - **Prompts & fixes** — 3 real prompts that mattered, **including one that failed**, and concrete mistakes the AI made and how you caught them;
   - **Retrospective** — what you would do differently, plus roughly how many hours you spent.
5. **README.md** — per M7.

## 12. Scoring

| Category | Weight |
|---|---|
| Working product (must-haves demonstrably working) | 30% |
| Code quality & security (M6 especially) | 25% |
| AI process — planning, prompt quality, catching the AI's mistakes | 25% |
| Git & repository hygiene | 10% |
| Process document | 10% |
| Defense | pass/fail gate |

## 13. Tech setup (system prerequisites)

| Component | Detail |
|---|---|
| **opencode Desktop** | Install fresh from [opencode.ai/download](https://opencode.ai/download) even if you tried it before. Pick Apple Silicon/Intel to match your Mac, or the x64 installer / .deb / .rpm for your OS. |
| **PHP 8.x + git** | The agent writes code, but your machine runs it. `brew install php` (macOS), XAMPP or `winget install PHP.PHP` (Windows), `sudo apt install php php-sqlite3 php-mysql composer` (Linux). git usually present on macOS/Linux; `winget install Git.Git` or [git-scm.com](https://git-scm.com) on Windows. **With SQLite you need no database server at all.**
| **Check** | `php -v` and `git --version` both answer in a terminal. |

## 14. Three pieces of honest advice

1. **Plan before you prompt** — opencode has a Plan mode; use it.
2. **Work in small steps** — giant "build everything" prompts produce giant unreviewable diffs and burn your free-model quota.
3. **Verify everything** — free models will happily write SQL injection; finding it is your job, and we notice when you do.
