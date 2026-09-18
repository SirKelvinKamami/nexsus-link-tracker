# LESSONS Learned — Nexsus Tracker Session Report

> **Purpose:** Document what was built, why decisions were made, and lessons for future sessions.
> **Project:** Nexsus Tracker (Laravel 9, PHP 8.2)
> **Hosting:** Render — `https://nexsus-link-tracker.onrender.com`
> **Phases Completed:** 4.0–4.5 (Task Management, NLP, Scheduling, Reminders, Analytics, Bio Page) + Phase 5 (Production Hardening & API Tokens)

---

## 1. Architecture Decisions & Why

### Dual AI Parser (Phase 4.1)
- **What:** `app/Services/AiParser.php` with OpenAI `gpt-4o-mini` primary + Anthropic `claude-3-haiku` fallback.
- **Why:** Redundancy — if one provider is down or key is missing, the other handles parsing.
- **Lesson:** Always implement fallback providers for external API dependencies. The fallback was tested and confirmed working without API keys.

### API Token Auth (Phases 4.0, 5)
- **What:** `ApiTokenAuth` middleware stores `_api_user_id` on the request object, NOT Laravel's `auth()`.
- **Why:** API token auth is stateless — Laravel's default auth system (session-based) doesn't work with Bearer tokens.
- **Lesson (CRITICAL):** Never use `auth()->id()` or `auth()->user()` in API token-authenticated code. Always use `$this->getAuthUserId()` or `$request->header('Authorization')` to get the user context. This was **Bug #2** — all task endpoints failed because `auth()->id()` returned null.

### Database-Backed Token Storage
- **What:** Tokens stored as SHA-256 hashes, plaintext returned only once on creation.
- **Why:** If the database is compromised, tokens can't be reversed. Prefix (first 12 chars) allows user identification without revealing full token.
- **Lesson:** This is a solid pattern — implement it early, don't try to invent a new one.

### Route Ordering (Bug #3)
- **What:** Specific routes (`/daily-digest`, `/schedule`) must be defined BEFORE parameterized routes (`/{id}`).
- **Why:** Laravel's routing matches `/{id}` first, catching `/daily-digest` as an ID.
- **Lesson:** Always define specific routes before parameterized ones in the same route group.

---

## 2. Critical Bugs & How They Were Fixed

### Bug #1: TaskController Not Extending BaseController
- **Symptom:** All task endpoints returned 500.
- **Root Cause:** TaskController extended `Controller` (Laravel's base) instead of project's `BaseController` which provides `success()` and `error()` helpers.
- **Fix:** Changed to `extends BaseController`.
- **Lesson:** When extending controllers in a project, always check what the project's base controller provides. Don't assume Laravel's default.

### Bug #2: auth()->id() Returns Null Under API Token Auth
- **Symptom:** All task operations failed — user_id was null, FK violations.
- **Root Cause:** `ApiTokenAuth` middleware sets `_api_user_id` on the Request object, NOT on Laravel's auth system. `auth()->id()` uses Laravel's auth which doesn't know about API tokens.
- **Fix:** Added `getAuthUserId()` helper that reads `$this->getUserId(request())` (which checks `_api_user_id`). Replaced ALL `auth()->id()` calls across both repos.
- **Lesson (CRITICAL):** Understand your auth middleware. If it bypasses Laravel's auth guard, you MUST use the middleware's mechanism to get user identity. Search entire codebase for `auth()->` in API controllers.

### Bug #3: Route Catch-All
- **Symptom:** `/daily-digest` and `/schedule` returned 404.
- **Root Cause:** Defined after `/{id}` in route file — caught by parameterized route.
- **Fix:** Reordered specific routes before `/{id}`.
- **Lesson:** Route file ordering matters. Use comments to mark which routes must come before others.

### Bug #4: Reminder $fillable Missing user_id
- **Symptom:** `POST /api/v1/reminders` returned 422 (NOT NULL constraint on user_id).
- **Root Cause:** `user_id` was in `$casts` and `$guarded` but not in `$fillable`. Mass assignment silently dropped it.
- **Fix:** Added `'user_id'` to `$fillable`.
- **Lesson:** When adding a column to a migration, ALWAYS add it to `$fillable` in the model. Check both `$fillable` AND `$guarded` — they can conflict.

### Bug #5: MySQL HOUR() Function in SQLite
- **Symptom:** Daily digest and analytics returned 500 on Render (SQLite).
- **Root Cause:** `ProductivityTracker::detectPeakHours()` used `HOUR(updated_at)` which is MySQL-specific. SQLite uses `strftime('%H', updated_at)`.
- **Fix:** Added DB driver detection: `strftime` for SQLite, `HOUR` for MySQL.
- **Lesson:** Never use database-specific SQL functions without abstraction. Check your production DB engine before writing raw queries.

### Bug #6: Daily Digest $now Variable Bug
- **Symptom:** Daily digest returned 500 — "Undefined variable $now".
- **Root Cause:** `$now = now()->hour` assigned an integer (the hour number) to `$now`, then code tried `$now->hour` on an integer.
- **Fix:** Separated into `$hour = now()->hour` and `$greeting = ...`.
- **Lesson:** Be careful with variable naming when chaining. Don't overwrite a variable with a different type.

### Bug #7: Reminder Logs Action Enum Mismatch
- **Symptom:** `POST /api/v1/reminders` returned 422 — CHECK constraint failed on `action` column.
- **Root Cause:** `ReminderEngine::createReminder()` called `recordLog($reminder, 'created', ...)` but the `reminder_logs.action` enum didn't include `'created'`.
- **Fix:** Updated migration enum to include `'created'` + new migration (raw SQL for SQLite compatibility).
- **Lesson:** When adding new enum values, create a new migration. SQLite enum changes require table recreation (use raw SQL, not `->change()` which fails with Doctrine DBAL).

### Bug #8: CORS Empty Origin Bug
- **Symptom:** `Access-Control-Allow-Origin` header returned empty string.
- **Root Cause:** `explode(',', '')` returns `[""]` (array with one empty string), not `[]`.
- **Fix:** Changed to `!empty(env('CORS_ORIGINS', '')) ? explode(',', ...) : []`.
- **Lesson:** Always validate the output of `explode()`. An empty string split by comma is not an empty array.

---

## 3. Bulk File Replacement Pitfalls

When replacing "LinkStack" with "Nexsus Tracker" across the codebase, several issues arose:

### View Name References
- **Problem:** Bulk replacement turned `view('linkstack.linkstack')` into `view('Nexsus Tracker.Nexsus Tracker')` — spaces break Laravel view resolution.
- **Fix:** Had to manually correct to `view('nexsus.nexsus')`.
- **Lesson:** After bulk replacements, search for spaces in view/class names that shouldn't have them.

### Directory Renames
- **Problem:** `resources/views/linkstack/` directory wasn't renamed by content replacement.
- **Fix:** Manually renamed directory to `resources/views/nexsus/` and file `linkstack.blade.php` to `nexsus.blade.php`.
- **Lesson:** Content replacement doesn't rename files/directories. Handle these separately.

### Config Key Changes
- **Problem:** `config('linkstack.user_cap')` became `config('nexsus.user_cap')` but no `config/nexsus.php` exists.
- **Fix:** These configs return null (handled gracefully by code). No functional impact.
- **Lesson:** Changing config keys requires checking if config files exist. If not, document that they return null by default.

---

## 4. Render Deployment Specifics

### DATABASE_URL Must NOT Be Set
- **Why:** Laravel's pgsql connection lets `DATABASE_URL` override `DB_HOST` with the internal hostname (`dpg-...-a`), which doesn't resolve from web services on Render's free tier.
- **Fix:** Set individual `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` vars instead.
- **Lesson:** Read Render's docs on Postgres free tier limitations. The comments in `render.yaml` document this — follow them.

### APP_KEY Handling
- **Why:** Render's `generateValue` produces raw base64 without Laravel's `base64:` prefix, causing "Unsupported cipher" 500s.
- **Fix:** Set `sync: false` for `APP_KEY` in render.yaml, manage manually via Render dashboard.
- **Lesson:** Know your deployment platform's quirks for env var generation.

### Session/Cache/Queue on Free Tier
- **Why:** Render free tier doesn't support Redis or private networking.
- **Fix:** All use `database` driver (sessions/cache/jobs in DB tables).
- **Lesson:** Choose deployment options that match your hosting tier's capabilities.

### Cold Start Latency
- **Observation:** ~4–5 second response times on Render free tier.
- **Cause:** Free tier spins down idle containers, causing cold starts.
- **Lesson:** Don't judge deployment health by response time alone — check health endpoints. Cold starts are normal on free tier.

---

## 5. Development Workflow Lessons

### Two-Repo Sync
- **Problem:** Code changes needed in both `nexsus-link-tracker-repo` and `LinkStack` directories.
- **Solution:** Used parallel agents to apply identical changes to both repos, then verified with grep.
- **Lesson:** When maintaining two codebases, use a systematic approach (read → edit → verify with grep) rather than relying on memory.

### Testing Strategy
- **Problem:** No PHPUnit tests existed. E2E tests were TypeScript/Playwright only.
- **Solution:** Created PHP test scripts (`test_api.php`) that use `file_get_contents` with stream context for API testing.
- **Lesson:** When no test framework exists, create lightweight PHP scripts that test endpoints directly. They're faster to write and run than setting up PHPUnit.

### Git Divergence Handling
- **Problem:** Local repo was ahead of `origin/main` by 1 commit, remote had 4 commits not locally present.
- **Solution:** Used `git merge origin/main -X ours` to resolve conflicts favoring local changes, then manually resolved one remaining conflict.
- **Lesson:** When working in a shared repo, pull frequently. If divergence happens, understand what `-X ours` does (keeps your version for conflicts) vs `-X theirs`.

---

## 6. Security Hardening Lessons

### Dead Code Identification
- **Finding:** `SecurityHeadersMiddleware.php` and `RateLimiterMiddleware.php` existed but were never registered in Kernel.php.
- **How Found:** Compared file system against Kernel.php registration.
- **Lesson:** Regularly audit for dead code. Files exist ≠ features active. Check Kernel.php for all middleware files.

### CORS Over-Exposure
- **Finding:** `allowed_origins: ['*']` allowed any website to make cross-origin API calls.
- **Fix:** Changed to env-driven empty default (same-origin only).
- **Lesson:** `['*']` is acceptable for development but dangerous in production. Always restrict CORS origins.

### Trust Hosts
- **Finding:** `TrustHosts` trusts all subdomains of the application URL.
- **Lesson:** In production, consider restricting to specific hosts. The current implementation is acceptable behind Cloudflare/Render but could be tightened.

---

## 7. Known Limitations & Open Items

| Item | Status | Notes |
|------|--------|-------|
| API token creation requires existing token | By design | Use global `API_TOKEN` env var or web UI for first token |
| No PHPUnit tests | Open | Could be added; PHP test scripts serve as interim |
| CORS_ORIGINS empty by default | By design | Same-origin only; configure via env for external clients |
| Cold start latency on Render | Expected | Free tier limitation; upgrade for performance |
| Two repo maintenance | Ongoing | Changes must be applied to both `nexsus-link-tracker-repo` and `LinkStack` |
| Render env vars from render.yaml | One-time only | render.yaml config applies when creating new service; existing services need manual env var setup |
| AI provider keys | Pending | OPENAI_API_KEY and ANTHROPIC_API_KEY not set; fallback parser works without them |

---

## 8. Key File Locations (Quick Reference)

### Core Application
| File | Purpose |
|------|---------|
| `app/Http/Controllers\Api/TaskController.php` | Task CRUD, parsing, scheduling, analytics |
| `app/Http/Controllers\Api/ReminderController.php` | Reminder CRUD |
| `app/Http/Controllers\Api/TokenController.php` | API token management |
| `app/Http/Controllers/Api/BaseController.php` | `success()`, `error()`, `getUserId()` helpers |
| `app/Http/Middleware/ApiTokenAuth.php` | Bearer token auth with scope checking |
| `app/Http/Middleware/SecurityHeadersMiddleware.php` | CSP, COOP, CORP, COEP headers |
| `app/Http/Middleware/RateLimiterMiddleware.php` | Custom rate limiting with JSON 429 |
| `app/Services/AiParser.php` | Dual AI provider parsing |
| `app/Services/TaskScheduler.php` | Smart scheduling with scoring |
| `app/Services/ReminderEngine.php` | Reminder lifecycle management |
| `app/Services/ProductivityTracker.php` | Analytics data collection |
| `app/Models/Task.php` | Task model with scopes |
| `app/Models/Reminder.php` | Reminder model |
| `app/Models/ApiToken.php` | Token model with `createToken()` |

### Configuration
| File | Purpose |
|------|---------|
| `config/tasks.php` | Task module config |
| `config/tasks_session.php` | Session rules |
| `config/cors.php` | CORS (env-driven origins) |
| `.env.example` | Development + production env template |
| `.env.docker` | Docker deployment env |
| `render.yaml` | Render deployment spec |

### Routes
| File | Purpose |
|------|---------|
| `routes/api.php` | Main API routes |
| `routes/api_reminders.php` | Reminder + scheduler routes |
| `routes/web.php` | Web routes (includes `/studio/tasks`) |

### Testing
| File | Purpose |
|------|---------|
| `test_api.php` | Lightweight API test script |

---

## 9. How to Continue This Work

If you're picking up this project mid-session:

1. **Check git status** — know what's committed and what isn't
2. **Check Render health** — `GET https://nexsus-link-tracker.onrender.com/api/v1/health/live`
3. **Check API token** — you need a valid Bearer token for API testing. Check `api_tokens` table or set `API_TOKEN` env var
4. **Check .env** — ensure production vars are set (APP_KEY, DB vars, API_TOKEN)
5. **Run test_api.php** — verify all endpoints work before making changes
6. **Check both repos** — changes often need to be applied to both `nexsus-link-tracker-repo` and `LinkStack`
7. **Read LESSONS.md** — this file, for context on why things are the way they are
