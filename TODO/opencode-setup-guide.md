# Setup Guide — opencode Desktop + Free AI Models

For the MiniRank assessment. Complete the **Day-0 pre-flight** (section 3) by **Saturday, 15.08.2026, end of day**.

## What you're setting up

The [opencode desktop app](https://opencode.ai/download) is an open-source AI coding agent with a graphical interface: a chat panel with tabbed sessions, a model picker, and a Review panel that shows the agent's changes as git diffs. You'll install it, connect it to free AI models, verify it answers, and learn the handful of features that matter for this test.

## 1. Install

### opencode Desktop

Download from **[opencode.ai/download](https://opencode.ai/download)**:

| OS | What to grab |
|---|---|
| macOS | DMG — pick **Apple Silicon** or **Intel** to match your Mac (About This Mac shows which) |
| Windows | x64 installer (.exe) — runs natively, **no WSL needed** |
| Linux | `.deb` (Ubuntu/Debian) or `.rpm` (Fedora/RHEL) |

Install it fresh even if you tried opencode before — the app updates frequently.

### PHP + git (the agent writes the code, but your machine runs it)

You need **PHP 8.x** and **git** available on your machine:

| OS | PHP | git |
|---|---|---|
| Windows | Easiest: [XAMPP](https://www.apachefriends.org) (PHP + MySQL + phpMyAdmin in one installer) — or `winget install PHP.PHP`. Make sure `php -v` works in a fresh terminal. | `winget install Git.Git` or [git-scm.com](https://git-scm.com) |
| macOS | `brew install php` | usually present; else `brew install git` |
| Linux | `sudo apt install php php-sqlite3 php-mysql composer` | `sudo apt install git` |

With SQLite you don't need any database server at all — one less thing to install and one less thing for us to set up when we review.

**Check:** `php -v` and `git --version` both answer in a terminal.

## 2. Get a model — pinned configuration

Use this order. Build with PRIMARY; move down the chain only when rate-limited.

| Priority | Provider | Model | Notes |
|---|---|---|---|
| PRIMARY | opencode Zen (built into the app) | **DeepSeek V4 Flash Free** — if it's missing from the list, use another **Free**-badged Zen model (e.g. MiMo V2.5 Free, Nemotron 3 Ultra Free) and mention the switch in your process doc | The free list rotates |
| FALLBACK 1 | Google Gemini (API key) | the free-tier **Flash** model available to your AI Studio key | Free API key via AI Studio, no card |
| FALLBACK 2 | OpenRouter | any model with the `:free` suffix | Hard caps: 20 req/min, **50 req/day** — emergency lane only |

### A. opencode Zen (PRIMARY)

1. **Create your free opencode account first:** go to [opencode.ai/auth](https://opencode.ai/auth) and pick **Continue with GitHub** — use the same GitHub account you'll use for the test repository (Continue with Google also works). That's the whole signup: **no credit card, no billing** — the free models cost nothing to use.
2. In the desktop app, click the **model name at the bottom of the prompt box** — this opens the model picker. Sign in with the same account if prompted (Zen's free models are built into the app).
3. Type "free" in the picker's search box — free models carry a **Free** badge. Select the pinned PRIMARY model.
4. Bookmark your workspace page at opencode.ai — its **Usage** tab lists every request with token counts, so you can watch your own consumption. Fun fact: a single trivial prompt already carries ~10k input tokens of context. That's why small, focused sessions matter.
5. Heads-up: free Zen models are free because they're in promo/trial periods, and your prompts may be used for model training. Don't paste anything personal — for this test app there's nothing sensitive anyway.

### B. Google Gemini (FALLBACK 1)

1. Get a free API key at [aistudio.google.com](https://aistudio.google.com) — Google account, no card.
2. Add it either in the app (add the Google provider, paste the key) or once in your opencode workspace page (**Zen → Bring Your Own Key → Google Gemini → Configure**). Then select it in the model picker.

### C. OpenRouter (FALLBACK 2)

1. Create a free account at [openrouter.ai](https://openrouter.ai) and generate an API key.
2. Add the OpenRouter provider in the app, paste the key, select the `:free` model you chose.
3. Know the caps: **20 requests/minute, 50 requests/day** on the free tier — an agent can burn 50 requests in under an hour. Emergency lane, not your highway.

## 3. Day-0 pre-flight (mandatory — by Saturday, 15.08.2026, end of day)

1. Create an empty folder and open it as a project in the opencode app.
2. Confirm your PRIMARY model is selected in the model picker.
3. Ask the agent: *"Create hello.php that prints the current date, then explain what you did."* — confirm the file appears in the file tree, then run it yourself: `php hello.php`.
4. Share the session — type `/share` (or session menu ⋯ → Share…); the link lands in your clipboard.
5. Reply to the email you received this test in with: that link + which models you have configured (PRIMARY + at least one fallback).

If anything fails, ask **now** — setup problems during the test window cost you your own time.

## 4. The features that matter

All of these sit in or under the prompt box:

| Feature | Where | Why you care |
|---|---|---|
| **Plan / Build** | first dropdown under the prompt box | Plan discusses without touching files; Build edits them. **Plan first**, then switch to Build. |
| **Model picker** | the model name under the prompt box | Your move when rate-limited: switch to the next model in the chain. Search "free" to see your free models. |
| **Effort selector** | last dropdown under the prompt box | Leave it on **Default** — higher settings think longer and spend your quota faster. |
| **Share session** | type `/share`, or session menu (⋯) → Share… | Creates the public session link (copied to your clipboard) — required for your submission. Unshare exists if you shared the wrong session. |
| **Review panel** | right side of the window | Shows every change as a git diff. **Read the diff before you build on top of it.** Blindly accepted diffs are how SQL injection gets in. |
| **New session per feature** | the **+** next to the session tabs | Keeps context small; long sessions overflow free-model context windows. Share the old session before moving on. |
| **AGENTS.md** | type `/init` — "guided AGENTS.md setup" | The instruction file the agent reads. Generate it, then improve it by hand with your conventions. |
| **`/` and `@`** | in the prompt box | `/` lists every command (`/undo`, `/redo`, `/review`, …); `@` attaches files as context to your prompt. |

The full reference is at [opencode.ai/docs](https://opencode.ai/docs/) — the app evolves quickly, so trust the app over screenshots.

## 5. Working within free limits (read this twice)

Free models mean smaller context windows and per-minute/per-day throttles. Candidates who do well treat this as an engineering constraint:

- **Plan first.** Agree the architecture in Plan mode before generating code. One good plan saves thirty bad requests.
- **Small steps.** "Implement the keyword CRUD (routes, queries, views) for the schema in schema.sql" beats "build the whole app." Giant prompts produce giant diffs, giant reviews, and burned quota.
- **New session per feature.** Long sessions overflow small context windows and the model starts forgetting your earlier decisions.
- **Rate-limited (429)?** Wait a minute or two, or switch to the next model in the chain. Note it in your process doc — handling limits is part of the assessment.
- **Verify as you go.** Run the app after every step. Free models produce plausible-looking code that is wrong — or insecure — more often than paid ones. Your review is the quality bar.
- **Budget across days.** Daily quotas usually reset every 24h; don't leave the must-haves for the last evening.

## 6. Troubleshooting

| Symptom | Fix |
|---|---|
| 429 / rate-limit errors | Wait briefly, then use the fallback chain |
| Empty or cut-off answers | Context overflow → new session, smaller prompt |
| Provider auth fails | Re-add the provider; check the key was copied fully |
| Agent can't find `php` (Windows) | Open a fresh terminal and check `php -v`; if it fails, PHP isn't on your PATH — re-run the XAMPP/winget install, then restart the opencode app |
| App misbehaves or won't start | Update to the latest version from [opencode.ai/download](https://opencode.ai/download) and retry; if still broken, reply to the test email **immediately** — don't lose a day to a tooling problem |
| Shared link doesn't open | Re-share the session; if still broken, mention it in your submission and we'll take the exported session instead |
