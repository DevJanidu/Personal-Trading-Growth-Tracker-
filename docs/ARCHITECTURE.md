# TradeGrowth — System Architecture

**Status:** Design document for Phase 0. No code in this repo implements the below yet; this is
the contract Phase 1+ implementation follows. Companion documents: `DATABASE_SCHEMA.md` (table
design this architecture operates on), `API_CONTRACTS.md` (the HTTP surface this architecture
exposes), `FRONTEND_ARCHITECTURE.md` (the consumer of that surface).

---

## 1. System context

```text
┌─────────────┐        HTTPS (cookies + CSRF)        ┌──────────────────┐
│   Browser   │ ────────────────────────────────────▶ │  Next.js Frontend │
│ (Trader)    │ ◀──────────────────────────────────── │  (App Router)     │
└─────────────┘                                        └────────┬─────────┘
                                                                  │ HTTPS
                                                                  │ /api/v1/*  (typed API client)
                                                                  ▼
                                                        ┌──────────────────┐
                                                        │  Laravel 13 API   │
                                                        │  + Sanctum        │
                                                        └───┬───────┬──────┘
                                                            │       │
                                              ┌─────────────┘       └─────────────┐
                                              ▼                                   ▼
                                    ┌──────────────────┐                ┌──────────────────┐
                                    │   PostgreSQL 16+  │                │      Redis        │
                                    │  (system of record)│               │ cache / queue /    │
                                    └──────────────────┘                │ rate limiting      │
                                                                          └──────────────────┘
                                              │
                                              ▼
                                    ┌──────────────────┐
                                    │  Object Storage    │
                                    │  local disk (dev)  │
                                    │  S3 / R2 (prod)     │
                                    └──────────────────┘
```

Frontend and backend are developed and deployed as two separate applications (SRS §114), talking
only over the versioned REST API — the frontend never touches Postgres/Redis directly.

---

## 2. Backend layering

Per SRS §47.2, every write and every non-trivial read goes through a fixed pipeline so analytics
logic is never duplicated across controllers:

```text
HTTP Request
    ↓
Route (routes/api.php, versioned under /api/v1)
    ↓
Controller (thin — orchestrates only)
    ↓
Form Request (validation + authorization gate via Policy)
    ↓
Action / Service (one focused class per use case, e.g. StoreTradeAction, CloseTradeAction)
    ↓
Analytics / Query layer (app/Analytics/*) for anything computed
    ↓
Model (Eloquent, relationships per DATABASE_SCHEMA.md §10)
    ↓
API Resource (response shaping — see API_CONTRACTS.md)
```

**Controllers never contain calculation logic.** A controller's job is: validate via Form
Request → call one Action → return a Resource. This is what makes the win-rate/profit-factor/
expectancy formulas in `ANALYTICS_FORMULAS.md` implementable as pure, independently-testable
classes.

### 2.1 Directory layout

```text
app/
├── Actions/                 # one class per write use-case
│   ├── Trades/
│   │   ├── StoreTradeAction.php
│   │   ├── UpdateTradeAction.php
│   │   ├── CloseTradeAction.php
│   │   └── RestoreTradeAction.php
│   ├── Accounts/
│   ├── Strategies/
│   └── Screenshots/
├── Analytics/                # pure calculation classes, one per metric family
│   ├── PerformanceMetricsCalculator.php   # win rate, PF, expectancy, avg R, streaks
│   ├── EquityCurveCalculator.php
│   ├── DrawdownCalculator.php
│   ├── StrategyAnalytics.php
│   ├── PairAnalytics.php
│   ├── SessionAnalytics.php
│   ├── WeekdayAnalytics.php
│   ├── MistakeAnalytics.php
│   ├── PsychologyAnalytics.php
│   ├── RiskAnalytics.php
│   ├── RuleComplianceAnalytics.php
│   ├── HistoricalSetupAnalyzer.php        # SRS §21/§39
│   └── SetupQualityScoreCalculator.php
├── DTOs/                      # typed value objects passed between Action → Analytics → Resource
├── Enums/                     # PHP 8.4 backed enums mirroring the Postgres enum types
├── Http/
│   ├── Controllers/Api/V1/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Policies/
├── Services/                  # cross-cutting services (e.g. BalanceReconciliationService)
├── Support/                   # small framework-agnostic helpers (money math, R-multiple math)
└── Jobs/                      # queued work — see §6
```

**Analytics classes never write to the database.** They accept a query scope (account, date
range, filters) and return DTOs. Anything that needs to *persist* a computed result (a snapshot
row, a monthly review) does so through an Action that calls the Analytics class and writes the
output — keeping "compute" and "persist" separate, so the same calculator serves both a live
dashboard request and a scheduled snapshot job.

---

## 3. Authentication: Sanctum SPA (cookie) mode

**Decision: cookie-based Sanctum SPA authentication, not personal-access-token/Bearer auth.**

Rationale: the frontend is a first-party SPA the trader logs into directly (not a third-party
integration consuming the API on someone else's behalf), so Sanctum's SPA mode is the mechanism
Laravel explicitly recommends for this shape — it gets CSRF protection, httpOnly session cookies
(no token sitting in `localStorage` where XSS could exfiltrate it), and automatic session
expiry/revocation through Laravel's normal session store, all "for free."
`personal_access_tokens` (already migrated in this repo) stays available for a future scenario —
a CLI import tool or a mobile app — but is not the web login path.

Flow:

```text
1. Browser → GET  /sanctum/csrf-cookie         (sets XSRF-TOKEN cookie)
2. Browser → POST /api/v1/auth/login           (credentials + X-XSRF-TOKEN header)
              ← Laravel sets session cookie (httpOnly, Secure, SameSite=Lax|None)
3. Every subsequent request → cookie sent automatically by the browser;
   Laravel's EnsureFrontendRequestsAreStateful middleware treats the configured
   frontend domain as "stateful" and authenticates via session instead of a token.
4. Browser → GET /api/v1/auth/user             (bootstraps client-side auth state)
5. Browser → POST /api/v1/auth/logout           (invalidates session, clears cookie)
```

Required Laravel configuration (Phase 1a, not this doc's scope to implement, only to specify):

- `SANCTUM_STATEFUL_DOMAINS` includes the frontend's host(s) (`localhost:3000` in dev).
- `SESSION_DOMAIN` set to the shared parent domain in production if frontend/backend are on
  sibling subdomains (e.g. `app.tradegrowth.io` / `api.tradegrowth.io` → `.tradegrowth.io`).
- CORS (`config/cors.php`): `supports_credentials = true`, `allowed_origins` restricted to the
  known frontend origin(s) — never `*` once credentials are involved.
- Cookies: `Secure` in production (HTTPS-only), `SameSite=None` only if frontend/backend are on
  different top-level domains and both are HTTPS; `SameSite=Lax` otherwise.

Password reset and email verification (SRS §48) use Laravel's standard notification-based flows,
exposed as the `/api/v1/auth/forgot-password` / `/reset-password` endpoints in
`API_CONTRACTS.md`. Email verification is scaffolded but not gated behind in V1 (single-user), so
it isn't a hard blocker for using the app immediately after registration.

---

## 4. Authorization

Every domain model gets a Laravel Policy (SRS §49). The rule is uniform and simple because of the
ownership chains fixed in `DATABASE_SCHEMA.md`:

```text
Policy::view/update/delete($user, $model) =>
    $model belongs to $user, resolved via the model's ownership path
    (direct user_id, or user_id on its parent trading_account/trade/strategy)
```

Concretely: `TradePolicy` checks `$trade->user_id === $user->id` (the denormalized column,
Decision #10 in `DATABASE_SCHEMA.md`); `TradeMistakePolicy` checks
`$tradeMistake->trade->user_id === $user->id`; `ScreenshotPolicy` checks through the
polymorphic parent. Every Form Request calls `$this->authorize()` — controllers never
skip the Policy layer, even for a currently-single-user system, because the Policy layer is what
makes multi-user conversion later a non-event (SRS §97).

Route-level: every `/api/v1/*` route except `/auth/login`, `/auth/forgot-password`,
`/auth/reset-password` requires `auth:sanctum`.

---

## 5. Multi-account / multi-user readiness

- Every domain table's ownership path terminates at `users.id` (§10 of `DATABASE_SCHEMA.md`).
- No table assumes "the" trading account — `trading_account_id` is always an explicit FK, never
  inferred from a session-global. The frontend's account selector (see
  `FRONTEND_ARCHITECTURE.md`) is what makes "current account" a *client-side* concept; the API
  is account-agnostic per-request (every trades/analytics endpoint takes `account_id` as a query
  parameter or resolves "all accounts" when omitted).
- No roles/permissions tables exist in V1 (per the user's explicit Phase 0 direction) — the
  Trader role is implicit (every authenticated user *is* a Trader over their own data). Adding an
  `Administrator` role later means adding a `role` column and a handful of `Gate::before` checks,
  not restructuring ownership.

---

## 6. Background jobs & scheduler

Queue driver: Redis (SRS §47.1). Jobs (SRS §85):

| Job | Trigger | Purpose |
|---|---|---|
| `ProcessScreenshotUpload` | screenshot POST | virus/type re-validation, thumbnail generation, move from temp to permanent disk path |
| `GenerateMonthlyReview` | scheduler, 1st of month | computes and writes one `monthly_reviews` row per active account for the prior month |
| `SnapshotAccountBalance` | trade closed **and** nightly scheduler | writes/upserts one `account_balance_snapshots` row |
| `GenerateAnalyticsSnapshot` | nightly (daily) + Sunday (weekly) + month-end (monthly) scheduler | writes `analytics_snapshots` rows per §8.4 of `DATABASE_SCHEMA.md` |
| `ExportTradesJob` | export request | large CSV/Excel generation, notifies user when ready (queued so a large export never blocks a request past the §96 1.5s budget) |
| `CheckGoalProgress` | nightly scheduler | writes `goal_progress` rows, flips `goals.status` to `achieved` when target crossed |

Laravel Scheduler entries (SRS §86), all defined in `routes/console.php`:

```text
daily   → SnapshotAccountBalance::dispatch() (backfill), GenerateAnalyticsSnapshot::dispatch('daily'),
          CheckGoalProgress::dispatch()
weekly  → GenerateAnalyticsSnapshot::dispatch('weekly')
monthly → GenerateAnalyticsSnapshot::dispatch('monthly'), GenerateMonthlyReview::dispatch()
```

---

## 7. Caching strategy

Two distinct caching concerns, not to be confused:

1. **Ephemeral request-scoped cache (Redis, TTL-based)** — for expensive real-time analytics
   queries that aren't yet worth a persisted snapshot (e.g. a one-off filtered "Analytics
   Comparison Engine" query, SRS §40). Key pattern (SRS §84):

   ```text
   analytics:{user_id}:{account_id}:{metric}:{filter_hash}
   ```

   Invalidated on: trade created/updated/deleted, account transaction created, strategy/taxonomy
   changed where the changed field affects the metric (e.g. renaming a strategy doesn't need to
   invalidate profit-factor cache; deleting one does). Implemented as a cache tag or explicit
   key-prefix flush — Redis tags via Laravel's `Cache::tags()` if using the Redis cache store's
   tagging support.

2. **Persisted historical snapshots (Postgres)** — `account_balance_snapshots`,
   `analytics_snapshots`, `monthly_reviews`, `goal_progress`. These are not a cache in the
   invalidate-on-write sense; they are the durable historical record trend charts read from, only
   ever appended to (never recomputed retroactively except by an explicit backfill command).

For a personal dataset (SRS §97: "1 user with thousands of trades"), most dashboard/analytics
queries can run uncached directly against indexed Postgres tables within the §96 performance
targets; Redis caching is an optimization layer added when real usage shows it's needed, not a
day-one requirement — this matches SRS §84's own "may initially be real-time" guidance.

---

## 8. Storage abstraction

All screenshot I/O goes through Laravel's `Storage` facade (never raw filesystem calls), so the
disk can change per environment without touching business logic:

```text
local  (dev)         → storage/app/public, served via symlink
s3 / r2 (staging/prod) → S3-compatible driver, config-only swap
```

Screenshots are never served from a public, guessable path. Reads go through a signed-URL
controller action (`GET /api/v1/screenshots/{screenshot}/view`) that checks the Policy, then
either streams the file (local disk) or redirects to a short-lived signed S3/R2 URL — satisfying
SRS §95 ("private screenshots require authentication") without needing the bucket itself to be
public.

---

## 9. Environment separation

| | Local | Staging | Production |
|---|---|---|---|
| DB | Postgres (Docker or native) | dedicated Postgres instance | managed Postgres, automated backups |
| Storage | local disk | S3/R2 bucket (staging prefix) | S3/R2 bucket (prod prefix) |
| Redis | local | dedicated instance | dedicated instance |
| Frontend URL | `localhost:3000` | `staging.<domain>` | `app.<domain>` |
| API URL | `localhost:8000` | `api-staging.<domain>` | `api.<domain>` |
| Secrets | `.env` (gitignored) | environment-injected | environment-injected, never in repo |

No environment shares a database, storage bucket, or credential set with another (SRS §115).

---

## 10. Security posture → concrete mechanisms

| SRS requirement (§93–95) | Mechanism |
|---|---|
| Authentication | Sanctum SPA cookie session (§3 above) |
| Authorization | Laravel Policies on every model, checked in every Form Request (§4 above) |
| CSRF protection | Sanctum's `XSRF-TOKEN` cookie + header, required on all state-changing requests |
| XSS prevention | React's default escaping on the frontend; API never returns raw HTML; CSP header set by Next.js middleware |
| SQL injection | Eloquent/query builder parameter binding exclusively — no raw string-interpolated SQL |
| Rate limiting | Laravel's `throttle` middleware backed by Redis, tighter limits on `/auth/*` |
| Secure file validation | MIME-type allow-list + magic-byte check (not just extension) + size limit, enforced in the screenshot Form Request (SRS §94) |
| Randomized storage filenames | `storage_path` generated server-side (UUID-based), never derived from the uploaded filename |
| Signed/private image access | Signed-URL streaming controller, §8 above |
| HTTPS in production | Enforced at the load balancer/Nginx layer; `APP_URL` and cookies both HTTPS-only |
| Secure cookies | `Secure`, `httpOnly`, `SameSite` per §3 above |
| Environment secrets outside repo | `.env` gitignored (already the case in this repo); production secrets injected by the deploy platform |
| Cross-user data isolation | Policy layer (§4) + every query scoped through the ownership chain — no endpoint accepts a raw ID without an ownership check |

---

## 11. Deployment topology (production target, SRS §114)

```text
Cloudflare (DNS, TLS termination, CDN for static assets)
    ↓
Nginx
    ├── Next.js (Node process, SSR/RSC)
    └── PHP-FPM → Laravel 13
                    ├── Postgres 16+
                    ├── Redis (cache, queue, rate limiting)
                    ├── Queue worker process (supervisor-managed)
                    └── Cron → Laravel Scheduler (`schedule:run` every minute)
Object storage: Cloudflare R2 or S3, accessed only via signed URLs
```

Not built in Phase 0 — this section exists so Phase 1's environment setup has a target to
provision toward.
