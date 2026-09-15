# Nexsus Link Tracker — Build Plan & Session Rules

**Project:** nexsus-link-tracker
**Type:** API-first link analytics, bio pages, forms, and landing pages
**Base:** LinkStack v4.8.6 (Laravel 9)
**Status:** Phase 1 Complete — Core Tracker

---

## Architecture

```
nexsus-link-tracker/           (Laravel 9, PHP 8.2)
├── app/
│   ├── Http/Controllers/
│   │   ├── UserController.php        # Web + API controllers
│   │   ├── Api/                      # REST API controllers (Phase 1)
│   │   └── ...
│   ├── Models/
│   │   ├── Link.php                  # Link model (random 9-digit IDs)
│   │   ├── LinkClick.php             # Click event log (Phase 1)
│   │   └── ...
│   ├── Services/
│   │   ├── ClickTracker.php          # Click recording service
│   │   └── ...
│   └── Support/
│       └── UserAgentParser.php       # Device/browser/OS detection
├── database/migrations/
├── routes/
│   ├── web.php                       # Web routes (auth + public)
│   └── api.php                       # REST API routes (Phase 1)
├── resources/views/                  # Blade templates
├── config/
│   └── advanced-config.php           # App settings
├── .env                              # Environment config
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

### Phase 2 — Bio Page & Documents (planned)
- [ ] Document/file upload module (PDF, DOCX, images)
- [ ] Document-type link block for bio page
- [ ] File preview/embed on bio page
- [ ] Download tracking per document

### Phase 3 — Forms Module (planned)
- [ ] `forms` table (title, description, slug, settings)
- [ ] `form_fields` table (label, type, options, order)
- [ ] `form_responses` table (form_id, session_id, answers JSON)
- [ ] Public form renderer at `/f/{slug}`
- [ ] Form builder in studio (drag-and-drop field editor)
- [ ] Response viewer + CSV export

### Phase 4 — Landing Pages (planned)
- [ ] `landing_pages` table (title, slug, content JSON, settings)
- [ ] Landing page renderer at `/lp/{slug}`
- [ ] Landing page builder in studio
- [ ] Form embed support in landing pages
- [ ] A/B testing framework

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

## API Endpoints (Phase 1)

Base URL: `{APP_URL}/api/v1`

### Authentication
All API requests require a Bearer token:
```
Authorization: Bearer {API_TOKEN}
```
Set `API_TOKEN` in `.env`. Generate with:
```bash
php artisan tinker --execute="echo bin2hex(random_bytes(32));"
```

### Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/links` | List all links (with click counts) |
| GET | `/api/v1/links/{id}` | Get single link + stats |
| GET | `/api/v1/clicks` | List click events (filterable) |
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
**Next:** Phase 2 (Bio Page & Documents)
