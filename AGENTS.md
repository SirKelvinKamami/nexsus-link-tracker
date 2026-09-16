# Nexsus Link Tracker — Build Plan & Session Rules

**Project:** nexsus-link-tracker
**Type:** API-first link analytics, bio pages, forms, and landing pages
**Base:** LinkStack v4.8.6 (Laravel 9)
**Status:** Phase 8 Complete — Docker Deployment

---

## Architecture

```
nexsus-link-tracker/           (Laravel 9, PHP 8.2)
├── app/
│   ├── Http/Controllers/
│   │   ├── UserController.php        # Web + API controllers
│   │   ├── DocumentController.php    # Document file serving
│   │   ├── FormController.php        # Forms CRUD (Phase 3)
│   │   ├── PublicFormController.php  # Public form renderer (Phase 3)
│   │   ├── LandingPageController.php # Landing pages CRUD (Phase 4)
│   │   ├── PublicLandingPageController.php # Public LP renderer (Phase 4)
│   │   ├── ProjectController.php     # Projects CRUD (Phase 5)
│   │   ├── WebhookController.php     # Webhooks CRUD (Phase 6)
│   │   └── Api/                      # REST API controllers (Phase 1)
│   ├── Models/
│   │   ├── Link.php                  # Link model (random 9-digit IDs)
│   │   ├── LinkClick.php             # Click event log (Phase 1)
│   │   ├── Form.php                  # Form model (Phase 3)
│   │   ├── FormField.php             # Form field model (Phase 3)
│   │   ├── FormResponse.php          # Form response model (Phase 3)
│   │   ├── LandingPage.php           # Landing page model (Phase 4)
│   │   ├── Project.php               # Project model (Phase 5)
│   │   ├── Webhook.php               # Webhook model (Phase 6)
│   │   ├── WebhookDelivery.php       # Webhook delivery log (Phase 6)
│   │   └── ...
│   ├── Services/
│   │   ├── ClickTracker.php          # Click recording service
│   │   ├── DocumentService.php       # Document file handling
│   │   ├── WebhookService.php        # Webhook dispatch (Phase 6)
│   │   └── ...
│   └── Support/
│       └── UserAgentParser.php       # Device/browser/OS detection
├── blocks/
│   └── document/                     # Document block type (Phase 2)
│       ├── config.yml
│       ├── handler.php
│       ├── form.blade.php
│       └── display.blade.php
├── config/
│   └── documents.php                 # Document settings (Phase 2)
├── database/migrations/
├── routes/
│   ├── web.php                       # Web routes (auth + public)
│   └── api.php                       # REST API routes (Phase 1)
├── resources/views/
│   ├── studio/
│   │   ├── documents.blade.php       # Document management view
│   │   ├── forms/                    # Form builder views (Phase 3)
│   │   ├── landing-pages/            # LP builder views (Phase 4)
│   │   └── projects/                 # Project management views (Phase 5)
│   └── ...
└── AGENTS.md                         # This file
```

---

## Tech Stack

| Component | Technology | Notes |
|-----------|-----------|-------|
| Backend | Laravel 9, PHP 8.2 | API-first, token auth |
| Database | SQLite (dev), MySQL/PostgreSQL (prod) | Configurable |
| Frontend | Blade + Hope UI (admin panel) | Bio page is public |
| Build | Laravel Mix + Tailwind CSS | `npx mix` compiles to root |
| Deploy | Docker / VPS | PHP 8.2 + Composer required |

---

## Phase Plan

### Phase 0 — Foundation ✅
- [x] PHP 8.2.33 + Composer 2.10.3 installed
- [x] LinkStack cloned and configured (SQLite, APP_KEY, seeds)
- [x] npm install + Tailwind/Mix build
- [x] Dev server running on 127.0.0.1:8000
- [x] Admin user: `admin` / `12345678`, page `@admin`

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

### Phase 4 — Landing Pages ✅
- [x] `landing_pages` table (title, slug, content JSON, settings)
- [x] Landing page renderer at `/lp/{slug}`
- [x] Landing page builder in studio (block editor)
- [x] Form embed support in landing pages
- [x] API endpoints for landing pages

### Phase 5 — Projects/Workspaces ✅
- [x] `projects` table (name, slug, settings)
- [x] Multi-project support (links, forms, landing pages per project)
- [x] Project switching in studio
- [x] Independent GA4/GTM per project
- [x] Project CRUD in studio
- [x] API endpoints for projects
- [x] ClickTracker records project_id with clicks

### Phase 6 — Integrations & Export ✅
- [x] Webhook support (click events, form submissions, page visits)
- [x] CSV export (clicks, links, form responses)
- [x] Webhook CRUD in studio + API
- [x] Webhook delivery log with retry
- [x] HMAC-SHA256 signature verification
- [x] WebhookService for event dispatch
- [x] API endpoints for webhooks
- [x] Sidebar navigation link

### Phase 7 — OAuth2 API Tokens ✅
- [x] `api_tokens` table (name, token hash, prefix, scopes, expiration)
- [x] Token CRUD in studio + API
- [x] Token scopes (read, write, admin)
- [x] Token expiration support
- [x] Token usage tracking (last used, count, IP)
- [x] HMAC-SHA256 token hashing
- [x] Backward compatibility with env API_TOKEN
- [x] API endpoints for token management
- [x] Sidebar navigation link

---

## API Endpoints (Phase 1)

Base URL: `{APP_URL}/api/v1`

### Authentication
All API requests require a Bearer token:
```
Authorization: Bearer {API_TOKEN}
```
Set `API_TOKEN` in `.env` for the default token. Or create tokens via:
- Studio: `/studio/api-tokens`
- API: `POST /api/v1/tokens`

Tokens support scopes (read, write, admin) and optional expiration.

### Endpoints

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
| GET | `/api/v1/forms/{id}` | Get single form + stats |
| GET | `/api/v1/forms/{id}/responses` | Get form responses |
| GET | `/api/v1/landing-pages` | List all landing pages |
| GET | `/api/v1/landing-pages/{id}` | Get single landing page + stats |
| GET | `/api/v1/projects` | List all projects |
| GET | `/api/v1/projects/{id}` | Get single project + stats |
| GET | `/api/v1/projects/{id}/stats` | Get project analytics summary |
| GET | `/api/v1/webhooks` | List all webhooks |
| GET | `/api/v1/webhooks/{id}` | Get single webhook + deliveries |
| POST | `/api/v1/webhooks` | Create a webhook |
| PUT | `/api/v1/webhooks/{id}` | Update a webhook |
| DELETE | `/api/v1/webhooks/{id}` | Delete a webhook |
| POST | `/api/v1/webhooks/{id}/test` | Test a webhook |
| GET | `/api/v1/tokens` | List all API tokens |
| POST | `/api/v1/tokens` | Create a token |
| DELETE | `/api/v1/tokens/{id}` | Delete a token |
| POST | `/api/v1/tokens/{id}/revoke` | Revoke a token |
| POST | `/api/v1/tokens/{id}/activate` | Activate a token |
| GET | `/api/v1/export/clicks` | Export clicks as CSV |
| GET | `/api/v1/export/links` | Export links as CSV |
| GET | `/api/v1/export/forms` | Export form responses as CSV |
| GET | `/api/v1/analytics/overview` | Aggregate stats (today/week/month/all) |
| GET | `/api/v1/analytics/top-links` | Top links by clicks |
| GET | `/api/v1/analytics/by-device` | Device breakdown |
| GET | `/api/v1/analytics/by-browser` | Browser breakdown |
| GET | `/api/v1/analytics/by-os` | OS breakdown |
| GET | `/api/v1/analytics/by-referrer` | Referrer breakdown |
| GET | `/api/v1/analytics/by-utm` | UTM source/medium/campaign breakdown |
| GET | `/api/v1/analytics/daily` | Daily click series (last 30 days) |
| GET | `/api/v1/page-views` | Page view stats |

### Query Parameters (clicks + analytics)
- `from` — Start date (YYYY-MM-DD)
- `to` — End date (YYYY-MM-DD)
- `link_id` — Filter by link ID
- `device_type` — Filter by device (mobile/tablet/desktop/bot)
- `browser` — Filter by browser
- `os` — Filter by OS
- `utm_source` — Filter by UTM source
- `utm_medium` — Filter by UTM medium
- `utm_campaign` — Filter by UTM campaign

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

---

## Docker Deployment

### Production Deployment
```bash
# Clone and enter directory
git clone https://github.com/SirKelvinKamami/nexsus-link-tracker.git
cd nexsus-link-tracker/LinkStack

# Copy environment file
cp .env.docker .env

# Edit .env with your settings
nano .env

# Build and start
./deploy.sh build

# Access at http://localhost:8080
# Admin: admin / 12345678
```

### Development (Docker)
```bash
# Start dev environment
docker-compose -f docker-compose.dev.yml up -d

# Access at http://localhost:8000
```

### Docker Commands
| Command | Description |
|---------|-------------|
| `./deploy.sh build` | Build and start all containers |
| `./deploy.sh start` | Start existing containers |
| `./deploy.sh stop` | Stop all containers |
| `./deploy.sh restart` | Restart all containers |
| `./deploy.sh logs` | View container logs |
| `./deploy.sh status` | Show container status |

### Docker Services
- **app**: PHP-FPM (Laravel)
- **nginx**: Web server (port 8080)
- **redis**: Cache/sessions
- **queue**: Background job worker
- **scheduler**: Cron task runner

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
3. Run lint/syntax checks if available
4. Commit with descriptive message
5. Push to `origin/main`

### Code Rules
- PHP 8.2+, Laravel conventions
- No comments unless asked
- Use `App\Services\ClickTracker` for click recording (not inline)
- Wrap analytics queries in try/catch (never break redirects)
- API responses always use `{ "success": bool, "data": ..., "error": ... }` format

### Database Rules
- Migrations must be reversible (`up()` + `down()`)
- Use `$table->foreign()->references()->on()->onDelete('cascade')`
- Never expose raw IPs (use `sha1($ip)`)
- Country column reserved for future geoip integration

### Testing
- Create test clicks: write a PHP script using `ClickTracker::record()`
- Test API: `curl -H "Authorization: Bearer {TOKEN}" http://127.0.0.1:8000/api/v1/links`
- Verify analytics: login → `/studio/analytics` → check cards + chart

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

**Last Updated:** 2026-09-15
**Authority:** SirKelvin Kamami (Boss)
**Phase 1 Status:** Complete (core tracker + API endpoints)
**Phase 2 Status:** Complete (bio page documents + download tracking)
**Phase 3 Status:** Complete (forms module + API endpoints)
**Phase 4 Status:** Complete (landing pages + API endpoints)
**Phase 5 Status:** Complete (projects/workspaces + API endpoints)
**Phase 6 Status:** Complete (webhooks + CSV export + API endpoints)
**Phase 7 Status:** Complete (OAuth2 API tokens + scopes)
**Phase 8 Status:** Complete (Docker deployment)
**Next:** Phase 9 (Production hardening, monitoring, backups)
