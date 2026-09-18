# User Data & Security — How Data Is Stored Safely

> **Purpose:** Document how user data is stored, secured, and managed for future logins.
> **Last Updated:** 2026-09-18

---

## 1. Users Table Schema

The `users` table stores all user account data:

| Column | Type | Description |
|--------|------|-------------|
| `id` | INTEGER (PK) | 6-digit random ID (non-sequential, prevents enumeration) |
| `name` | VARCHAR(255), UNIQUE | Display name |
| `email` | VARCHAR(255), UNIQUE | Login email |
| `email_verified_at` | DATETIME, NULL | Email verification timestamp |
| `password` | VARCHAR(255) | **Bcrypt hash** (never stored plaintext) |
| `handle` | VARCHAR(255) | User's public handle (formerly `littlelink_name`) |
| `bio` | TEXT | User biography (formerly `littlelink_description`) |
| `role` | VARCHAR(255) | `user`, `editor`, or `admin` |
| `block` | VARCHAR(255) | `yes` or `no` — blocks user from accessing platform |
| `image` | VARCHAR(255) | Profile image path |
| `provider` | VARCHAR(255) | OAuth provider name (if social login) |
| `provider_id` | VARCHAR(255) | OAuth provider user ID |
| `description` | TEXT | Additional description |
| `theme` | VARCHAR(255) | User's selected theme |
| `auth_as` | INTEGER | Unused (legacy) |
| `remember_token` | VARCHAR(255) | "Remember me" token |
| `created_at` / `updated_at` | DATETIME | Timestamps |

**User ID Generation:** Random 6-digit numbers (100000–999999), not sequential. This prevents user enumeration via ID guessing.

---

## 2. Password Security

### How Passwords Are Stored
- **Hashing Algorithm:** Bcrypt (config/hashing.php)
- **Rounds:** 10 (configurable via `BCRYPT_ROUNDS` env var, default 10)
- **Storage:** Hash stored in `users.password` column — **plaintext never stored, never logged**
- **Retrieval:** Passwords are irrecoverable from the hash. Comparison uses `Hash::check()` which handles bcrypt verification.

### Password Lifecycle
| Event | Security Measure |
|-------|-----------------|
| Registration | Password hashed with bcrypt before INSERT |
| Login | Hash compared with `Hash::check()` |
| Password Reset | Token expires in 60 minutes, throttle 60 seconds between resets |
| Password Confirmation | Times out after 3 hours (10800 seconds) |

### Login Throttling
- **Standard routes:** 60 requests/minute per user/IP
- **Auth routes (login/register):** 6 requests/minute per user/IP
- **Password reset:** 6 requests/minute
- **CAPTCHA:** Added after 3 failed login attempts (not hard 429)

---

## 3. Session Management

### Session Configuration (config/session.php)
| Setting | Value | Security Implication |
|---------|-------|---------------------|
| `driver` | `database` (on Render) | Sessions stored in DB, not files |
| `lifetime` | 120 minutes (2 hours) | Session expires after 2 hours of inactivity |
| `expire_on_close` | false | Session persists after browser close |
| `encrypt` | false | Laravel encrypts the cookie payload by default |
| `cookie` | `nexsus_link-tracker_session` | Custom cookie name (not default Laravel) |
| `secure` | `env('SESSION_SECURE_COOKIE')` | HTTPS-only when set to true |
| `http_only` | true | JavaScript cannot access session cookie |
| `same_site` | `lax` | CSRF protection for cross-site requests |

### Session Storage
- **Production (Render):** Database driver — sessions stored in `sessions` table
- **Development:** File driver (default)
- **Docker:** Redis driver (in docker-compose)

### Session Table Schema
| Column | Description |
|--------|-------------|
| `id` | Session ID (varchar, PK) |
| `user_id` | User who owns this session (nullable for guest sessions) |
| `ip_address` | Client IP address |
| `user_agent` | Browser user agent string |
| `payload` | Serialized session data |
| `last_activity` | Unix timestamp of last activity |

**Session Security:**
- Sessions are stored server-side (database/redis) — client only has the session ID
- `http_only` prevents XSS-based session hijacking
- `same_site=lax` prevents CSRF on cross-site requests
- `secure` flag ensures cookies only sent over HTTPS
- Sessions expire after 2 hours of inactivity

---

## 4. API Token Storage

### Token Security Model
| Aspect | Implementation |
|--------|---------------|
| **Plaintext token** | Generated (80 random chars), returned **ONCE** on creation |
| **Database storage** | SHA-256 hash of plaintext — irreversible |
| **Identification** | First 12 chars of plaintext stored as `prefix` — for display/masking |
| **Token lookup** | Client sends plaintext → middleware hashes it → compares with DB hash |

### API Tokens Table Schema
| Column | Description |
|--------|-------------|
| `id` | Primary key |
| `user_id` | FK → users.id, CASCADE on delete |
| `name` | Human-readable token name |
| `token` | SHA-256 hash (unique, indexed) |
| `prefix` | First 12 chars of plaintext (for display) |
| `scopes` | JSON array: `["read"]`, `["write"]`, `["admin"]` |
| `last_used_at` | JSON timestamp of last use |
| `last_used_ip` | JSON array of SHA-1 hashed IPs |
| `usage_count` | Number of times used |
| `is_active` | Active/inactive toggle |
| `expires_at` | Optional expiration date |

### Scope Enforcement
| HTTP Method | Required Scope |
|-------------|---------------|
| GET, HEAD | `read` |
| POST, PUT, PATCH | `write` |
| DELETE | `admin` |

### Token Usage Tracking
Every authenticated request calls `recordUsage()`:
- Updates `last_used_at` timestamp
- Increments `usage_count`
- Appends SHA-1 hash of client IP to `last_used_ip` array
- Provides audit trail without storing identifiable IPs in plaintext

---

## 5. Authentication Guards

### Three Guards (config/auth.php)
| Guard | Driver | Provider | Use Case |
|-------|--------|----------|----------|
| `web` | session | users (Eloquent) | Web UI login (session-based) |
| `api` | token | users (Eloquent) | API token auth (Bearer tokens) |
| `admin` | session | admins (Eloquent) | Admin panel (separate user table) |

### Auth Flow
**Web Login (session):**
1. User submits credentials via web form
2. Auth guard validates against `users` table
3. Session created in `sessions` table
4. Session cookie set (http_only, same_site=lax)
5. User's ID stored in session payload

**API Login (token):**
1. Client sends `Authorization: Bearer {token}` header
2. `ApiTokenAuth` middleware:
   - Extracts token from header or query param
   - Checks against global `API_TOKEN` env var (system-level token)
   - SHA-256 hashes token, looks up in `api_tokens` table
   - Verifies `is_active = true`
   - Checks expiration
   - Determines scope from HTTP method
   - Records usage (IP, timestamp, count)
   - Sets `_api_user_id` on Request object
3. Downstream code reads `$request->_api_user_id` for user context

### Critical: auth() vs _api_user_id
**DO NOT** use `auth()->id()` or `auth()->user()` in API token-authenticated code. The `ApiTokenAuth` middleware bypasses Laravel's auth system and stores user ID as `_api_user_id` on the Request object. Use `$request->_api_user_id` or helper methods instead.

---

## 6. Database Overview (32 Tables)

### Core Application Tables
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `users` | User accounts | name, email, password, role |
| `sessions` | Session storage | id, user_id, payload, last_activity |
| `api_tokens` | API authentication tokens | token (hash), scopes, usage_count |
| `password_resets` | Password reset tokens | email, token |
| `cache` | Cache store | key, value, expiration |
| `jobs` | Queue jobs | queue, payload, attempts |
| `failed_jobs` | Failed queue jobs | uuid, exception |

### Content Tables (forked from LinkStack)
| Table | Purpose | Owner |
|-------|---------|-------|
| `links` | User link/button entries | users |
| `link_clicks` | Click analytics | users, links |
| `buttons` | Button configurations | system |
| `pages` | Static page content | system |
| `visits` | Visit tracking | system |
| `link_types` | Link type definitions | system |

### Feature Tables (Phase 4.x)
| Table | Purpose | Owner |
|-------|---------|-------|
| `tasks` | User tasks | users |
| `task_items` | Task checklist items | tasks |
| `schedule_rules` | Smart scheduling rules | users |
| `reminders` | User reminders | users |
| `reminder_logs` | Reminder action logs | reminders |

### System Tables
| Table | Purpose |
|-------|---------|
| `projects` | User projects |
| `forms` | Form builder |
| `form_fields` | Form field definitions |
| `form_responses` | Form submissions |
| `landing_pages` | Landing page builder |
| `webhooks` | Webhook configurations |
| `webhook_deliveries` | Webhook delivery logs |
| `share_links` | Share/link shortcuts |
| `social_accounts` | OAuth connections |
| `user_privacies` | Per-user privacy settings |

### Foreign Key Relationships
All FK relationships use `ON DELETE CASCADE` for owner-based tables (tasks, reminders, forms, etc.) — deleting a user deletes all their data. Shared tables (link_clicks, share_links) have `ON DELETE NO ACTION` to preserve analytics data.

---

## 7. Data Privacy

### User Privacy Settings (user_privacies table)
| Setting | Default | Description |
|---------|---------|-------------|
| `profile_visible` | true | Profile visible to others |
| `email_visible` | false | Email not shown publicly |
| `links_visible` | true | Links visible to others |
| `analytics_visible` | false | Analytics data private |
| `allow_comments` | false | Comments disabled by default |
| `default_link_permission` | private | Links are private by default |

### Sensitive Data Handling
| Data Type | How It's Stored | How It's Handled |
|-----------|----------------|-----------------|
| Passwords | Bcrypt hash (irreversible) | Never logged, never returned in API |
| API Tokens | SHA-256 hash | Plaintext shown once, never stored |
| Session Data | Server-side (DB/Redis) | Client sees only session ID |
| User IPs (audit) | SHA-1 hash | Not stored in plaintext |
| Reminder IPs | SHA-1 hash | Not stored in plaintext |
| Emails | Plaintext (required for login) | Only visible to user and admins |

---

## 8. Security Architecture Summary

### Layers of Protection
```
Client Request
    │
    ▼
┌──────────────────────────┐
│  HTTPS (TLS)             │  ← Encrypted in transit
│  (FORCE_HTTPS, Render)   │
└──────────┬───────────────┘
           │
    ▼
┌──────────────────────────┐
│  SecurityHeadersMiddleware│  ← CSP, X-Frame-Options, XSS, COOP/CORP
│  (registered globally)   │
└──────────┬───────────────┘
           │
    ▼
┌──────────────────────────┐
│  TrustProxies             │  ← Trusts Render/Cloudflare proxies
│  TrustHosts               │  ← Validates Host header
└──────────┬───────────────┘
           │
    ▼
┌──────────────────────────┐
│  CORS (config/cors.php)  │  ← Origins restricted (env-driven)
│  HandleCors               │
└──────────┬───────────────┘
           │
    ▼
┌──────────────────────────┐
│  Session/Cookie Guard    │  ← http_only, same_site, secure
│  (web guard)              │
│  OR                       │
│  ApiTokenAuth Middleware  │  ← Token validation, scope check,
│  (api guard)              │    usage tracking, IP logging
└──────────┬───────────────┘
           │
    ▼
┌──────────────────────────┐
│  Route Throttle           │  ← 60 req/min (api group)
│                           │
│  RateLimiterMiddleware    │  ← Custom 429 with X-RateLimit headers
│  (rate.limit, optional)   │
└──────────┬───────────────┘
           │
    ▼
┌──────────────────────────┐
│  Controller Logic         │  ← User-scoped queries (forUser, getUserId)
│                           │  ← Input validation
│                           │  ← Proper error handling
└──────────────────────────┘
```

### What's Protected
| Threat | Protection |
|--------|-----------|
| XSS | CSP headers, `http_only` cookies, input validation |
| CSRF | `same_site=lax` cookies, CSRF token (web routes), auth middleware (API) |
| Brute Force | Login throttle (6 req/min), CAPTCHA after 3 fails |
| SQL Injection | Eloquent ORM, parameterized queries |
| Session Hijacking | `http_only`, `secure`, `same_site`, IP tracking |
| User Enumeration | Random 6-digit IDs, generic error messages |
| Token Leakage | SHA-256 storage, plaintext shown once, usage tracking |
| Data Breach | Bcrypt passwords (irreversible), hashed IPs |
| Clickjacking | `X-Frame-Options: SAMEORIGIN` |
| MIME Sniffing | `X-Content-Type-Options: nosniff` |

---

## 9. Known Limitations & Recommendations

| Item | Status | Recommendation |
|------|--------|----------------|
| `auth()->id()` in some views | Present (UserController.php line 269 uses Auth) | Web context OK, but should use consistent helper |
| `users` table has legacy columns | `littlelink_name`, `littlelink_description` still in DB | Migration exists to rename them; needs to be run |
| CORS default | Empty (safe) but needs configuration for external clients | Set `CORS_ORIGINS` env var when needed |
| `TrustProxies` | `$proxies = '*'` | Acceptable behind Cloudflare/Render; tighten if self-hosted |
| Session encryption | `encrypt => false` | Laravel encrypts cookie payload; consider enabling for sensitive data |
| `auth_as` column | Unused | Remove in future cleanup |
| `SESSION_SECURE_COOKIE` | Depends on env var | Set to `true` explicitly in production env files |

---

## 10. How to Log In

### Web Login
1. Navigate to `/login` (or `/studio/login`)
2. Enter email and password
3. Session created — user ID stored in `sessions` table
4. Session cookie set with `http_only`, `same_site=lax`

### API Authentication
1. Create a token via `POST /api/v1/tokens` (requires existing token or web session)
2. Use the returned plaintext token in the `Authorization: Bearer {token}` header
3. Token is validated on every request by `ApiTokenAuth` middleware
4. Scopes determine which endpoints you can access

### First Token (No Existing Tokens)
Use the global `API_TOKEN` environment variable to create the first token via API, or log in via the web UI and navigate to `/studio/api-tokens`.
