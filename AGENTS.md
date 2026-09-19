# Nexsus Link Tracker — Build Plan & Session Rules

**Project:** nexsus-link-tracker
**Type:** API-first link analytics, bio pages, forms, and landing pages
**Framework:** Laravel 9 (PHP 8.2)
**Hosting:** Render — `https://nexsus-link-tracker.onrender.com`
**Database:** PostgreSQL on Render (SQLite for local development)
**Status:** Phase 3 Complete — Forms Module | **Phase 4 Complete — Task Management & Reminders** | **Phase 4.0-4.5 All Complete**

---

## Architecture

```
nexsus-link-tracker/           (Laravel 9, PHP 8.2)
├── app/
│   ├── Http/Controllers/
│   │   ├── UserController.php        # Web + API controllers
│   │   ├── DocumentController.php    # Document file serving
│   │   ├── Api/                      # REST API controllers
│   │   │   ├── LinkController.php    # existing
│   │   │   ├── ClickController.php   # existing
│   │   │   ├── AnalyticsController.php  # existing
│   │   │   ├── DocumentController.php  # existing
│   │   │   ├── FormController.php    # existing
│   │   │   ├── TaskController.php    # NEW — Phase 4.0
│   │   │   ├── ReminderController.php  # NEW — Phase 4.3
│   │   │   └── ScheduleRuleController.php  # NEW — Phase 4.2
│   │   └── ...
│   ├── Models/
│   │   ├── Link.php                  # Link model (random 9-digit IDs)
│   │   ├── LinkClick.php             # Click event log (Phase 1)
│   │   ├── User.php                  # existing (reused for tasks)
│   │   ├── Task.php                  # NEW — Phase 4.0
│   │   ├── TaskItem.php              # NEW — Phase 4.0
│   │   └── ...
│   ├── Services/
│   │   ├── ClickTracker.php          # existing
│   │   ├── DocumentService.php       # existing
│   │   ├── TaskScheduler.php         # NEW — Phase 4.2 (scheduled)
│   │   ├── ReminderEngine.php        # NEW — Phase 4.3 (scheduled)
│   │   ├── AiParser.php              # NEW — Phase 4.1 (scheduled)
│   │   └── ProductivityTracker.php   # NEW — Phase 4.4 (scheduled)
│   └── Support/
│       └── UserAgentParser.php       # existing
├── blocks/
│   ├── link/                         # existing
│   ├── document/                     # existing
│   ├── task-reminder/                # NEW — Phase 4.5 (task/reminder display block)
│   ├── task-dashboard/               # NEW — Phase 4.5 (stat card widget)
│   └── ...
│   └── ...
├── config/
│   ├── tasks.php                     # NEW — Phase 4.0
│   └── ...
│   └── ...
├── database/
│   ├── migrations/
│   │   ├── ...                       # existing
│   │   ├── 2026_09_16_000001_create_tasks_table.php    # NEW — Phase 4.0
│   │   └── 2026_09_16_000002_create_task_items_table.php  # NEW — Phase 4.0
│   └── ...
├── resources/views/
│   ├── studio/
│   │   ├── tasks.blade.php           # NEW — Phase 4.0
│   │   └── ...
│   └── ...
└── AGENTS.md                         # This file (updated)
```

---

## Tech Stack

| Component | Technology | Notes |
|-----------|-----------|-------|
| Backend | Laravel 9, PHP 8.2 | API-first, token auth |
| Database | SQLite (dev), MySQL/PostgreSQL (prod) | Configurable |
| Frontend | Blade + Livewire (admin panel) | Bio page is public |
| Build | Laravel Mix + Tailwind CSS | `npx mix` compiles to root |
| Deploy | Docker / VPS | PHP 8.2 + Composer required |
| Task Scheduler | Laravel Queues + Scheduler | Reuses existing infrastructure |

---

## Phase Plan

### Phase 0 — Foundation ✅
- [x] PHP 8.2.33 + Composer 2.10.3 installed
- [x] Nexsus Tracker cloned and configured (SQLite, APP_KEY, seeds)
- [x] npm install + Tailwind/Mix build
- [x] Dev server running on 127.0.0.1:8000
- [x] Admin user: `admin`, page `@admin` (password from `ADMIN_PASSWORD`; `password` in dev, randomly generated and printed to the deploy log in production — see CONTRIBUTING.md)

### Phase 1 — Core Tracker ✅
- [x] `link_clicks` table (20 columns: link_id, user_id, UTM fields, device/browser/OS, etc.)
- [x] `LinkClick` model + `ClickTracker` service
- [x] UA parsing (device type, browser, OS — regex-based, no deps)
- [x] Click logging in `/going/{id}` and `/vcard/{id}` routes
- [x] Page view recording via `visits()->increment()` in `littlelink()` / `littlelinkhome()`
- [x] Analytics dashboard at `/studio/analytics` (cards, 30-day chart, top links, breakdowns)
- [x] GA4/GTM snippet injection (`GTM_ID` / `GA4_ID` env vars)
- [x] `dataLayer` push on outbound link clicks
- [x] Nexsus branding (APP_NAME, user name, meta tags)
- [x] **REST API endpoints** (Phase 1 addition)

### Phase 2 — Bio Page & Documents ✅
- [x] Document/file upload module (PDF, DOCX, images, ZIP)
- [x] Document-type link block for bio page
- [x] File preview/embed on bio page (icons, file size, download button)
- [x] Download tracking per document (via ClickTracker)
- [x] Document storage config (`config/documents.php`)
- [x] DocumentService for file handling
- [x] DocumentController for serving files
- [x] Studio document management view (`/studio/documents`)
- [x] Sidebar navigation link

### Phase 3 — Forms Module ✅
- [x] `forms` table (title, description, slug, settings)
- [x] `form_fields` table (label, type, options, order)
- [x] `form_responses` table (form_id, session_id, answers JSON)
- [x] Public form renderer at `/f/{slug}`
- [x] Form builder in studio (field editor)
- [x] Response viewer + CSV export
- [x] API endpoints for forms

### Phase 4 — Task Management & Reminders
- [x] **Phase 4.0** — Task Management Foundation
  - [x] `tasks` table migration
  - [x] `task_items` table migration
  - [x] `Task` model + `TaskItem` model (relationships to User)
  - [x] `TaskController` (CRUD + complete + parse + dailyDigest)
  - [x] API routes (all in routes/api.php)
  - [x] View: `resources/views/studio/tasks.blade.php`
  - [x] Config: `config/tasks.php`
- [x] **Phase 4.1** — NLP Task Parsing (AiParser service)
  - [x] `app/Services/AiParser.php` (OpenAI primary + Anthropic fallback)
  - [x] POST `/api/v1/tasks/parse` endpoint
  - [x] `app/Http/Requests/TaskParseRequest.php`
  - [x] Dual provider: OpenAI (`gpt-4o-mini`) + Anthropic (`claude-3-haiku-4-20250901`)
- [x] **Phase 4.2** — Smart Scheduling (TaskScheduler service)
  - [x] `app/Services/TaskScheduler.php` (calendar-aware, priority-based, conflict resolution)
  - [x] `GET /api/v1/tasks/schedule` — suggested time slots
  - [x] `POST /api/v1/tasks/{id}/schedule` — schedule a single task
  - [x] `POST /api/v1/tasks/recalculate` — recalculate all pending tasks
  - [x] `GET /api/v1/tasks/rules` + `POST /api/v1/tasks/rules` — schedule rules
  - [x] `app/Models/ScheduleRule.php` + migration `2026_09_16_000003_create_schedule_rules_table.php`
  - [x] Conflict detection and auto-resolution
  - [ ] TaskParserController dedicated endpoint (optional)
- [x] **Phase 4.3** — Reminder Engine (reminders tables, ReminderEngine, queue delivery)
  - [x] `app/Models/Reminder.php` + `app/Models/ReminderLog.php`
  - [x] `database/migrations/2026_09_16_000004_create_reminders_table.php`
  - [x] `database/migrations/2026_09_16_000005_create_reminder_logs_table.php`
  - [x] `app/Services/ReminderEngine.php` (create, send, process, respond)
  - [x] `app/Http/Controllers\Api\ReminderController.php` (index, store, send, respond)
  - [x] Routes: GET/POST `/api/v1/reminders`, POST `/api/v1/reminders/{id}/send`, POST `/api/v1/reminders/{id}/respond`
  - [x] `config/tasks.php` reminders enabled
  - [x] `app/Mail/TaskReminderMail.php` + view `emails.task-reminder`
- [x] **Phase 4.4** — Analytics & Insights (ProductivityTracker, task analytics endpoints, enhanced daily digest)
  - [x] `app/Services/ProductivityTracker.php` (overview, completionTrend, priorityDistribution, productivityScore, focusTime, topCompletedTasks, yesterdayReview)
  - [x] `TaskController::analyticsOverview` — GET /api/v1/tasks/analytics/overview
  - [x] `TaskController::completionTrend` — GET /api/v1/tasks/analytics/completion-trend
  - [x] `TaskController::priorityDistribution` — GET /api/v1/tasks/analytics/priority-distribution
  - [x] `TaskController::productivityScore` — GET /api/v1/tasks/analytics/productivity-score
  - [x] `TaskController::topCompleted` — GET /api/v1/tasks/analytics/top-completed
  - [x] Enhanced `TaskController::dailyDigest` — today's plan, yesterday's review, AI insights
  - [ ] Frontend analytics dashboard (charts, productivity gauge) — see Figma prototype (backend APIs ready)
- [x] **Phase 4.5** — Bio Page Integration
  - [x] `blocks/task-reminder/` — bio page block (display, form, handler, config)
  - [x] `blocks/task-dashboard/` — dashboard widget (stat cards, config)
  - [x] Model accessors for Figma alignment: `Task::priority_label`, `Task::status_label`, `Task::progress`, `Task::due_date`, `Reminder::task_title`, `Reminder::due_info`, `Reminder::section`
  - [ ] React frontend pages (Dashboard, AI Parser, Calendar, Reminders, Analytics, Focus, Digest) — APIs ready

### Phase 5 — Projects/Workspaces (planned)
- [ ] `projects` table (name, slug, settings)
- [ ] Multi-project support (links, analytics per project)
- [ ] Project switching in studio
- [ ] Independent GA4/GTM per project

### Phase 6 — Integrations & Export (planned)
- [ ] Webhook support (click events → external URLs)
- [ ] CSV/API export
- [ ] Zapier/Make integration
- [ ] OAuth2 API tokens for third-party apps

---

## API Endpoints (Phase 1 + Phase 4.0)

Base URL: `{APP_URL}/api/v1`

### Authentication
All routes require Bearer token auth:
```
Authorization: Bearer {API_TOKEN}
```
Set `API_TOKEN` in `.env`. Generate with:
```bash
php artisan tinker --execute="echo bin2hex(random_bytes(32));"
```

### Existing Endpoints (Phase 1)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/links` | List all links (with click counts) |
| GET | `/api/v1/links/{id}` | Get single link + stats |
| GET | `/api/v1/clicks` | List click events (filterable) |
| GET | `/api/v1/documents` | List all documents |
| GET | `/api/v1/documents/{id}` | Get single document |
| GET | `/api/v1/documents/{id}/stats` | Get document download stats |
| DELETE | `/api/v1/documents/{id}` | Delete a document |
| GET | `/api/v1/forms` | List all forms |
| GET | `/api/v1/forms/{id}` | Get single form |
| GET | `/api/v1/forms/{id}/responses` | Get form responses |
| GET | `/api/v1/forms/{id}/stats` | Get form stats |
| GET | `/api/v1/analytics/overview` | Aggregate stats |
| GET | `/api/v1/analytics/top-links` | Top links by clicks |
| GET | `/api/v1/analytics/by-device` | Device breakdown |
| GET | `/api/v1/analytics/by-browser` | Browser breakdown |
| GET | `/api/v1/analytics/by-os` | OS breakdown |
| GET | `/api/v1/analytics/by-referrer` | Referrer breakdown |
| GET | `/api/v1/analytics/by-utm` | UTM breakdown |
| GET | `/api/v1/analytics/daily` | Daily click series (last 30 days) |
| GET | `/api/v1/page-views` | Page view stats |

### New Endpoints (Phase 4.0 + 4.1 + 4.2 + 4.3 + 4.4)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/tasks` | List tasks (filterable by status, priority, search) |
| GET | `/api/v1/tasks/{id}` | Get single task + subtasks + items |
| POST | `/api/v1/tasks` | Create task |
| PUT | `/api/v1/tasks/{id}` | Update task |
| DELETE | `/api/v1/tasks/{id}` | Delete task |
| POST | `/api/v1/tasks/{id}/complete` | Complete task + cascade |
| POST | `/api/v1/tasks/parse` | Parse natural language (OpenAI + Anthropic) |
| GET | `/api/v1/tasks/daily-digest` | Daily digest (today's plan, yesterday's review, AI insights) |
| GET | `/api/v1/tasks/schedule` | Suggested time slots |
| POST | `/api/v1/tasks/{id}/schedule` | Schedule a single task |
| POST | `/api/v1/tasks/recalculate` | Recalculate all pending tasks |
| GET / POST | `/api/v1/tasks/rules` | List / create schedule rules |
| GET | `/api/v1/tasks/analytics/overview` | Task analytics overview (created, completed, avg priority, rate) |
| GET | `/api/v1/tasks/analytics/completion-trend` | Daily completion trend (line chart data) |
| GET | `/api/v1/tasks/analytics/priority-distribution` | Priority distribution (donut chart data) |
| GET | `/api/v1/tasks/analytics/productivity-score` | Productivity score (0-100) with factors |
| GET | `/api/v1/tasks/analytics/top-completed` | Top completed tasks table |
| GET | `/api/v1/reminders` | List reminders |
| POST | `/api/v1/reminders` | Create reminder |
| POST | `/api/v1/reminders/{id}/send` | Send reminder now |
| POST | `/api/v1/reminders/{id}/respond` | Record user response |
| GET / POST / PUT | `/api/v1/scheduler/rules` | List / create / update scheduling rules |

### Response Format
```json
{
  "success": true,
  "data": { ... },
  "meta": {
    "total": 150,
    "per_page": 50,
    "current_page": 1
  }
}
```

### Error Format
```json
{
  "success": false,
  "error": "Unauthorized",
  "message": "Invalid or missing API token"
}
```

### Query Parameters (tasks)
- `status` — Filter by status (pending, in_progress, completed, cancelled, snoozed)
- `priority` — Filter by priority (1-5)
- `search` — Search by title
- `project` — Filter by project name

---

## Session Rules (for AI collaboration)

### Session Start (MANDATORY)
1. Read this `AGENTS.md` — current phase and status
2. Check `git status` — uncommitted changes
3. Check `.env` — config is correct
4. Check server: `curl http://127.0.0.1:8000/` — app is running

### Session End (MANDATORY)
1. Update status in this `AGENTS.md` (mark completed items)
2. Run `php artisan migrate:status` — verify migrations
3. Run `php artisan route:list` — verify routes
4. Run lint/syntax checks if available
5. Commit with descriptive message
6. Push to `origin/main`

### Code Rules
- PHP 8.2+, Laravel conventions
- No comments unless asked
- Use `App\Services\AiParser` for task parsing (not inline)
- Use `App\Services\ClickTracker` for click recording (not inline)
- Use `App\Services\ProductivityTracker` for task analytics (not inline)
- Use `App\Services\ReminderEngine` for reminder operations (not inline)
- Wrap analytics queries in try/catch (never break redirects)
- API responses always use `{ "success": bool, "data": ..., "error": ... }` format
- **Task rules**: All task controller methods in try/catch, use `report($e)` for errors
- **Task models**: Use `$fillable`, never `$guarded`; foreign keys cascade on delete
- **Task migrations**: Must be reversible (`up()` + `down()`)
- **AiParser rules**: Primary = OpenAI, fallback = Anthropic. Return structured JSON with title, priority, description, due_datetime, project, estimated_duration, subtasks, dependencies, provider.
- **Figma prototype**: UI alignment reference at `D:\AFK Dev Environ\Nexsus Link Tracker\Build Premium Web App Prototype.zip` — extract for component patterns, design tokens, and screen layouts

### Database Rules
- Migrations must be reversible (`up()` + `down()`)
- Use `$table->foreign()->references()->on()->onDelete('cascade')`
- Never expose raw IPs (use `sha1($ip)`)
- Country column reserved for future geoip integration
- New task tables: `tasks`, `task_items` — foreign keys to `users` and `tasks` respectively

### Testing
- Create test tasks: `php artisan tinker --execute="App\Models\Task::create(['user_id' => 1, 'title' => 'Test', 'priority' => 3]);"`
- Test API: `curl -H "Authorization: Bearer {TOKEN}" http://127.0.0.1:8000/api/v1/tasks`
- Verify task completion: `curl -X POST -H "Authorization: Bearer {TOKEN}" http://127.0.0.1:8000/api/v1/tasks/{id}/complete`
- Test daily digest: `curl -H "Authorization: Bearer {TOKEN}" http://127.0.0.1:8000/api/v1/tasks/daily-digest`
- Test AI parser (OpenAI): `curl -X POST -H "Authorization: Bearer {TOKEN}" -H "Content-Type: application/json" -d '{"text": "Finish the report by Friday, high priority"}' http://127.0.0.1:8000/api/v1/tasks/parse`
- Test AI parser (Anthropic fallback): Set `OPENAI_API_KEY=` (empty) in .env, then run same parse test
- Verify both providers: Check response `data.provider` field is "openai" or "anthropic"
- Test reminders: `curl -H "Authorization: Bearer {TOKEN}" -X POST -H "Content-Type: application/json" -d '{"task_id": 1, "trigger_type": "time", "trigger_value": "2026-09-17 12:00:00", "channel": "email"}' http://127.0.0.1:8000/api/v1/reminders`
- Test reminder send: `curl -X POST -H "Authorization: Bearer {TOKEN}" http://127.0.0.1:8000/api/v1/reminders/{id}/send`
- Test reminder respond: `curl -X POST -H "Authorization: Bearer {TOKEN}" -H "Content-Type: application/json" -d '{"response": "acknowledged"}' http://127.0.0.1:8000/api/v1/reminders/{id}/respond`
- List reminders: `curl -H "Authorization: Bearer {TOKEN}" http://127.0.0.1:8000/api/v1/reminders`
- Test task analytics overview: `curl -H "Authorization: Bearer {TOKEN}" "http://127.0.0.1:8000/api/v1/tasks/analytics/overview?period=7d"`
- Test completion trend: `curl -H "Authorization: Bearer {TOKEN}" "http://127.0.0.1:8000/api/v1/tasks/analytics/completion-trend?days=7"`
- Test priority distribution: `curl -H "Authorization: Bearer {TOKEN}" http://127.0.0.1:8000/api/v1/tasks/analytics/priority-distribution`
- Test productivity score: `curl -H "Authorization: Bearer {TOKEN}" http://127.0.0.1:8000/api/v1/tasks/analytics/productivity-score`
- Test top completed: `curl -H "Authorization: Bearer {TOKEN}" "http://127.0.0.1:8000/api/v1/tasks/analytics/top-completed?limit=5"`
- Test daily digest: `curl -H "Authorization: Bearer {TOKEN}" http://127.0.0.1:8000/api/v1/tasks/daily-digest`

---

## Bug Fixes (API Testing Phase)

### 2026-09-18: Critical bugs found via API testing and fixed

| Bug | Root Cause | Fix |
|-----|-----------|-----|
| Task operations returned 401/500 | TaskController extended `Controller` instead of `BaseController` — `success()`/`error()` helpers missing | Changed to `extends BaseController` |
| All task endpoints failed with null user_id | `auth()->id()` returned null under API token auth (middleware sets `_api_user_id` on request, not Laravel auth) | Added `getAuthUserId()` helper using `$this->getUserId(request())`; replaced all `auth()->id()` calls |
| `/daily-digest` and `/schedule` returned 404 | Routes defined after `/{id}` parameter route, caught by parameterized route | Reordered: specific routes before `/{id}` |
| Reminder creation returned 422 (NOT NULL user_id) | `Reminder.php` `$fillable` missing `'user_id'` | Added `'user_id'` to `$fillable` |
| Daily digest 500 (SQLite) | `$now = now()->hour` assigned integer, then `$now->hour` on integer | Separated into `$hour` variable and `$greeting` variable |
| Analytics 500 (SQLite) | `ProductivityTracker::detectPeakHours()` used MySQL `HOUR()` function | Added DB driver detection: `strftime('%H', ...)` for SQLite, `HOUR(...)` for MySQL |
| Reminder creation 422 (CHECK constraint) | `reminder_logs.action` enum lacked `'created'` value | Updated existing migration enum + new migration `2026_09_16_000006` (raw SQL for SQLite compatibility) |

### 2026-09-19: Onboarding broken end to end — users could not sign up or log in

| Bug | Root Cause | Fix |
|-----|-----------|-----|
| No login ever succeeded | `resources/views/auth/login.blade.php` was UTF-16LE encoded (written via a PowerShell redirect in `d4255c0`). Blade compiles UTF-8 bytes, so no directive matched and the template was emitted verbatim — no `<form>`, no `@csrf`, so every login POST failed CSRF | Re-encoded UTF-8/LF, stripped BOM, repaired mojibake'd translation key. CAPTCHA revert preserved |
| Production stuck on a half-migrated schema | `2026_09_16_000006` used `$table->enum(...)->change()`, which routes through Doctrine DBAL (no `enum` type) and threw on **Postgres**, not just MySQL. `entrypoint.sh` ran `migrate --force \|\| true`, so the failure was swallowed and every later migration never ran | Rewrote as raw driver-specific DDL (pgsql CHECK swap / sqlite rebuild / mysql MODIFY) |
| Migrations aborted on SQLite | `2026_09_18_000001` called `renameColumn` twice in one `Schema::table` closure; SQLite rejects more than one per modification | Split into separate guarded `Schema::table` calls, idempotent in both directions |
| 500 on `/` and `/dashboard` | `findFile()` pointed at `assets/Nexsus Tracker/images/` (real dir `assets/nexsus/`) — rebrand artifact, made `scandir()` fatal | Repointed all 178 `assets/Nexsus Tracker/` references |
| Every public profile page 500'd | `littlelink()`/`littlelinkhome()` computed `$handle` but never passed it to the view, which dereferences it | Added `'handle' => $handle` to both `view()` calls |
| `View [Nexsus Tracker.modules.meta] not found` | 15 `@include`/`@extends` used the literal `Nexsus Tracker.` view prefix | Repointed to `nexsus.` |
| User cap and single-user mode silently unenforced | 8 `config('Nexsus Tracker.*')` calls resolved to a non-existent config file and returned `null` | Repointed to `config('nexsus.*')` |
| Social signup and installer failed | `users.role` column default was legacy `'user'`, rejected by `users_role_check` (`viewer\|commenter\|editor\|admin`) | Migration `2026_09_19_000002` aligns the default and repairs legacy rows; `role` now explicit at every creation site |
| User inserts broke after the rename | `User::setHandleAttribute` wrote to both `handle` and `littlelink_name`, putting a non-existent column in every INSERT | Mutators now target only the column that exists; resolver memoized per request |

### 2026-09-19: Security and pipeline

| Issue | Detail | Fix |
|-------|--------|-----|
| Default admin credentials in production | `AdminSeeder` hard-coded `admin@admin.com` / `12345678` and `db:seed --force` runs on every boot | Driven by `ADMIN_EMAIL`/`ADMIN_PASSWORD`; random generated password in production. **Existing accounts are not fixed by this** — rotate with `php artisan nexsus:rotate-admin-password --all-weak` |
| Repo contents publicly downloadable | Docroot is the repo root and `docker/nginx.conf` had none of the protections `.htaccess` defines — logs, a sqlite DB, manifests, `artisan` and internal docs were served, and any `.php` (incl. `vendor/`) was executable | Only `/index.php` executes; sensitive paths, types and root metadata denied |
| BOM emitted into responses | 13 `resources/lang/*/messages.php` files were UTF-8-with-BOM; a BOM before `<?php` is echoed when that locale loads | Stripped |
| Red CI still deployed | `render.yaml` had `autoDeploy: true`, so all 8 consecutive red runs shipped anyway — the direct cause of the half-migrated schema | `autoDeploy: false` + a `deploy` job gated on both test jobs (needs `RENDER_DEPLOY_HOOK_URL` secret) |
| Registration smoke passed on failure | CI posted `littlelink_name` where the form requires `handle`; validation failure also returns 302, so the assertion passed | Corrected the field, added a logout → login round trip (login had no coverage at all), plus guards failing on any UTF-16/BOM source or surviving `Nexsus Tracker` identifier |

---

## Das-Hub Integration

The tracker API is consumed by the DAS Creative CRM (`das-hub` repo).

**Service file:** `das-hub/services/trackerService.ts`
**Usage:**
```typescript
import { trackerService } from './services/trackerService';

// Get all links with click counts
const links = await trackerService.getLinks();

// Get analytics for a specific link
const analytics = await trackerService.getLinkAnalytics(linkId, {
  from: '2024-01-01',
  to: '2024-01-31'
});

// Get daily click series for charts
const daily = await trackerService.getDailyClicks();
```

**Config:** Set `VITE_TRACKER_URL` and `VITE_TRACKER_TOKEN` in das-hub `.env`.

---

**Last Updated:** 2026-09-19
**Authority:** SirKelvin Kamami (Boss)
**Phase 1-3 Status:** Complete
**Phase 4.0-4.5 Status:** Complete (Task Management, NLP Parsing, Smart Scheduling, Reminders, Analytics, Bio Page Integration)
**Phase 5 Status:** Complete (Production hardening, API token management)
**Next:** Ongoing — monitor, optimize, expand

**Open operator actions (2026-09-19):**
1. Add the `RENDER_DEPLOY_HOOK_URL` repository secret before merging the deploy gate — without it the `deploy` job fails instead of shipping, and confirm Render's **Auto-Deploy** toggle reads **No**.
2. Rotate the seeded admin password *at* deploy, not after: login is currently broken in production, which is the only thing making `admin@admin.com` / `12345678` unreachable. Restoring login makes it usable. Run `php artisan nexsus:rotate-admin-password --all-weak` in the Render shell.
