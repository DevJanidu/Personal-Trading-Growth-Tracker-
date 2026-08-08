# TradeGrowth — API Contracts

**Status:** Design document. Defines the Laravel API surface the frontend consumes. No routes are
implemented yet — see `IMPLEMENTATION_PLAN.md` for build order. Table/column names referenced
below are defined in `DATABASE_SCHEMA.md`; auth mechanism is defined in `ARCHITECTURE.md` §3.

---

## 1. Conventions

**Base URL:** `/api/v1` (SRS §73).

**Auth:** Sanctum SPA session cookie on every route except `POST /auth/login`,
`POST /auth/forgot-password`, `POST /auth/reset-password`. State-changing requests
(`POST`/`PUT`/`PATCH`/`DELETE`) require the `X-XSRF-TOKEN` header (Sanctum's CSRF cookie value).

**Content type:** `application/json` for all bodies except screenshot upload endpoints
(`multipart/form-data`).

**Success envelope** (single resource — a Laravel API Resource):

```json
{ "data": { "...resource fields..." } }
```

**Success envelope (collection, paginated)**:

```json
{
  "data": [ { "...": "..." } ],
  "meta": {
    "current_page": 1,
    "per_page": 25,
    "total": 143,
    "last_page": 6
  },
  "links": {
    "first": "…", "last": "…", "prev": null, "next": "…"
  }
}
```

**Error envelope** (all 4xx/5xx):

```json
{
  "message": "Human-readable summary.",
  "errors": {
    "field_name": ["Specific validation message."]
  }
}
```

`errors` is present only for `422 Unprocessable Entity` (Laravel Form Request validation
failures); other error codes return `message` only.

**Standard status codes:** `200` read/update ok, `201` created, `204` deleted (no body),
`401` unauthenticated, `403` unauthorized (authenticated but not the owner), `404` not found or
not owned (never leak existence of another user's record — a resource that exists but isn't
yours returns `404`, not `403`), `422` validation failure, `429` rate limited.

**Dates:** all request/response datetimes are ISO 8601 UTC (`2026-08-09T14:30:00Z`); the frontend
converts to the user's `timezone` (from `GET /auth/user`) for display only.

**IDs in URLs:** route-model-bound by `uuid`, not the internal sequential `id` (per
`DATABASE_SCHEMA.md` §12 Decision #9) — e.g. `/api/v1/trades/{trade:uuid}`.

**Server-authoritative calculation rule:** endpoints that accept trade financial fields
(`gross_profit_loss`, `net_profit_loss`, `r_multiple`, `planned_rr`, `achieved_rr`, etc.) accept
them as *inputs where the trader manually overrides a broker discrepancy*, but the server always
recomputes and persists the authoritative value server-side per `ANALYTICS_FORMULAS.md` — the
client-submitted value is never trusted as-is for anything analytics reads (SRS §109). Response
bodies always reflect the server-computed values.

---

## 2. Authentication API

### `POST /api/v1/auth/login`
Request:
```json
{ "email": "trader@example.com", "password": "…" }
```
Response `200`:
```json
{ "data": { "id": "uuid", "name": "…", "email": "…", "timezone": "…", "default_currency": "USD", "theme": "system" } }
```
`422` on invalid credentials (`errors.email`).

### `POST /api/v1/auth/logout`
No body. `204`.

### `GET /api/v1/auth/user`
Response `200`: same user shape as login. `401` if not authenticated.

### `POST /api/v1/auth/forgot-password`
Request: `{ "email": "…" }`. Response `200`: `{ "message": "Reset link sent." }` (always `200`
regardless of whether the email exists, to avoid account enumeration).

### `POST /api/v1/auth/reset-password`
Request: `{ "token": "…", "email": "…", "password": "…", "password_confirmation": "…" }`.
Response `200` on success, `422` on invalid/expired token.

---

## 3. Trading Accounts API

### `GET /api/v1/accounts`
Query: none (returns all of the user's accounts, not paginated — expected count is small).
Response `200`: array of Account resources:
```json
{
  "data": [{
    "id": "uuid", "name": "Funded Account #01", "account_type": "funded",
    "broker": "…", "currency": "USD",
    "initial_balance": "5000.0000", "current_balance": "5325.0000", "current_equity": "5325.0000",
    "growth_percent": "6.5000", "status": "active",
    "max_overall_drawdown_percent": "10.0000", "max_daily_drawdown_percent": "5.0000",
    "current_overall_drawdown_percent": "1.5700", "current_daily_drawdown_percent": "0.5000",
    "created_at": "…"
  }]
}
```
`growth_percent`/`current_*_drawdown_percent` are server-computed per `ANALYTICS_FORMULAS.md`,
not stored columns.

### `POST /api/v1/accounts`
Request: all `trading_accounts` fields from `DATABASE_SCHEMA.md` §3.1 except server-managed ones
(`current_balance` defaults to `initial_balance`, `current_equity` defaults to same).
Response `201`: Account resource.

### `GET /api/v1/accounts/{account}`
Response `200`: Account resource (same shape as list item).

### `PUT /api/v1/accounts/{account}`
Request: any subset of editable fields. Response `200`.

### `DELETE /api/v1/accounts/{account}`
Soft-deletes. `204`. `409 Conflict` if the account has trades and a hard-delete was explicitly
requested via `?force=true` (soft delete alone always succeeds).

### `GET /api/v1/accounts/{account}/transactions`
Query: `type`, `date_from`, `date_to`, `page`. Response `200`: paginated
`account_transactions` list.

### `POST /api/v1/accounts/{account}/transactions`
Request: `{ "type": "deposit", "amount": "500.0000", "transaction_date": "2026-08-01", "notes": "…" }`.
Response `201`. Triggers server-side recalculation of `current_balance`.

### `GET /api/v1/accounts/{account}/growth`
Query: `period` (`all|ytd|6m|3m|month|week|custom`), `date_from`, `date_to` (when `period=custom`).
Response `200`:
```json
{
  "data": {
    "starting_balance": "5000.0000", "current_balance": "5325.0000",
    "highest_balance": "5410.0000", "net_trading_profit": "325.0000",
    "growth_percent": "6.5000", "deposits": "0.0000", "withdrawals": "0.0000",
    "series": [{ "date": "2026-08-01", "balance": "5000.0000", "equity": "5000.0000" }]
  }
}
```

### `GET /api/v1/accounts/{account}/drawdown`
Query: same period params as `/growth`.
Response `200`:
```json
{
  "data": {
    "current_drawdown_percent": "1.5700", "maximum_drawdown_percent": "4.2000",
    "highest_equity": "5410.0000", "current_equity": "5325.0000",
    "recovery_needed_percent": "1.5900",
    "largest_losing_streak": 4, "drawdown_duration_days": 6,
    "daily_drawdown_limit_percent": "5.0000", "todays_drawdown_percent": "0.5000",
    "remaining_daily_buffer_percent": "4.5000",
    "overall_drawdown_limit_percent": "10.0000", "remaining_overall_buffer_percent": "8.4300",
    "series": [{ "date": "2026-08-01", "drawdown_percent": "0.0000" }]
  }
}
```
Funded-account-only fields (`daily_drawdown_limit_percent` etc.) are `null` for non-funded types.

---

## 4. Trades API

### `GET /api/v1/trades`
Query filters (all optional, combinable — this is the canonical filter set reused by
`/reports/*` too): `account_id`, `date_from`, `date_to`, `symbol`, `asset_class`, `strategy_id`,
`strategy_setup_id`, `trading_session_id`, `market_condition_id`, `entry_model_id`,
`setup_grade_id`, `direction`, `outcome`, `status`, `tag_id`, `mistake_category_id`,
`psychology_state_id`, `min_risk_percent`, `max_risk_percent`, `min_r`, `max_r`, `weekday`,
`followed_plan`, `search` (free text over journal fields + symbol + trade_number), `sort`
(`trade_date|net_profit_loss|r_multiple`, default `trade_date`), `direction=asc|desc` for sort,
`page`, `per_page` (default 25, max 100).

Response `200`: paginated Trade resources (list shape — summary fields only, not full journal):
```json
{
  "data": [{
    "id": "uuid", "trade_number": 42, "trade_date": "2026-08-05",
    "symbol": "XAUUSD", "direction": "long", "strategy": { "id": "uuid", "name": "…" },
    "setup_grade": { "id": "uuid", "name": "A+" },
    "actual_entry_price": "2410.50000000", "actual_exit_price": "2418.20000000",
    "risk_percent": "0.5000", "r_multiple": "2.1000", "net_profit_loss": "105.0000",
    "outcome": "win", "trading_session": { "id": "uuid", "name": "New York" },
    "status": "closed", "journal_completeness_percent": 65,
    "mistake_count": 1
  }],
  "meta": { "...": "..." }
}
```

### `POST /api/v1/trades`
Request body mirrors the `trades` table fields from `DATABASE_SCHEMA.md` §4.1 that are
user-supplied (excludes `id`, `uuid`, `user_id`, computed financial fields, `setup_quality_score`,
timestamps). Nested optional arrays: `tag_ids: [uuid]`,
`checklist: [{ "strategy_checklist_item_id": "uuid", "is_checked": true }]`. Server validates
per SRS §87: account ownership, symbol present, direction valid, non-negative risk, strategy
ownership, setup belongs to the given strategy, `exit_at >= entry_at`.
Response `201`: full Trade detail resource (see below).

### `GET /api/v1/trades/{trade}`
Response `200`: full detail resource — list fields above plus: all price/risk/result fields,
`trading_account`, `market_condition`, `entry_model`, `partial_exits: []`, `journal: {…}`,
`checklist_snapshot: []`, `mistakes: []`, `psychology: {…}`, `rule_violations: []`, `tags: []`,
`screenshots: { before: [], during: [], after: [] }`.

### `PUT /api/v1/trades/{trade}`
Same body shape as `POST`. Financial fields are recalculated server-side after merge.
Response `200`: full detail resource.

### `DELETE /api/v1/trades/{trade}`
Soft-deletes. `204`.

### `POST /api/v1/trades/{trade}/close`
Request: `{ "actual_exit_price": "…", "exit_at": "…", "fees": "…", "commission": "…", "swap": "…" }`
(or, if partial exits were recorded via a separate endpoint, no price needed — server derives from
partials). Transitions `status → closed`, computes `outcome`, `net_profit_loss`, `r_multiple`,
etc., and writes an `account_balance_snapshots` row. Response `200`: full detail resource.

### `POST /api/v1/trades/{trade}/restore`
Un-soft-deletes. `200`: full detail resource. `409` if the owning account was hard-deleted.

### `POST /api/v1/trades/{trade}/partial-exits`
Request: `{ "exit_price": "…", "quantity": "…", "percentage_closed": "…", "exited_at": "…", "notes": "…" }`.
Response `201`: created `trade_partial_exits` row; triggers server recalculation of the parent
trade's aggregate result fields.

---

## 5. Screenshot API

### `GET /api/v1/trades/{trade}/screenshots`
Response `200`: `{ "data": { "before": [...], "during": [...], "after": [...] } }`.

### `POST /api/v1/trades/{trade}/screenshots`
`multipart/form-data`: `file`, `type` (`before|during|after`), `caption?`, `timeframe?`,
`sort_order?`. Validates MIME type (jpeg/png/webp), size (≤ configured max, default 10 MB per
SRS §94). Response `201`: Screenshot resource (`{ id, uuid, type, caption, timeframe, sort_order,
url }` — `url` is the signed-view endpoint below, never the raw storage path).

### `PUT /api/v1/trades/{trade}/screenshots/{screenshot}`
Request: `{ "caption?", "timeframe?", "sort_order?", "annotation_note?" }`. Response `200`.

### `DELETE /api/v1/trades/{trade}/screenshots/{screenshot}`
`204`.

### `GET /api/v1/screenshots/{screenshot}/view`
Policy-checked signed streaming/redirect endpoint (see `ARCHITECTURE.md` §8). Not a JSON
endpoint — returns the image binary (local disk) or a `302` to a short-lived signed URL (S3/R2).

The same four endpoints exist under `/api/v1/strategies/{strategy}/screenshots` for the
`type=example` case, sharing the same underlying `ScreenshotController` per
`DATABASE_SCHEMA.md`'s polymorphic `screenshots` table.

---

## 6. Strategy (Playbook) API

### `GET /api/v1/strategies`
Query: `status` (`active|archived`), `search`. Response `200`: array (not paginated — small set).

### `POST /api/v1/strategies`
Request: `strategies` table fields (§7.1) that are user-editable.
Response `201`.

### `GET /api/v1/strategies/{strategy}`
Response `200`: full detail incl. `setups: []`, `rules: []`, `checklist_items: []`,
`example_screenshots: []`.

### `PUT /api/v1/strategies/{strategy}`
Response `200`.

### `DELETE /api/v1/strategies/{strategy}`
Archives (`status = archived`) by default. `?force=true` attempts hard delete — `409` if any
trade references it (per `DATABASE_SCHEMA.md` §12 Decision #7), otherwise soft-deletes.

### `GET /api/v1/strategies/{strategy}/setups`
### `POST /api/v1/strategies/{strategy}/setups`
Request: `{ "name": "…", "description?": "…" }`. Response `201`.

### `GET /api/v1/strategies/{strategy}/checklist`
### `POST /api/v1/strategies/{strategy}/checklist`
Request: `{ "label": "…", "description?", "weight": 1, "is_required": false, "sort_order": 0 }`.
Response `201`.

### `GET /api/v1/strategies/{strategy}/rules` · `POST /api/v1/strategies/{strategy}/rules`
Request: `{ "text": "…", "sort_order": 0 }`. Response `201`.

### `GET /api/v1/strategies/{strategy}/performance`
Response `200`: the SRS §20.4 historical-performance block — same shape as one row of
`GET /analytics/strategies` (below), scoped to this strategy only.

---

## 7. Taxonomy APIs (sessions, market conditions, entry models, setup grades, mistake categories, psychology states, tags, trading rules)

One uniform contract, reused across all seven (per `DATABASE_SCHEMA.md` §12 Decision #2's
"generic controller" note):

```text
GET    /api/v1/trading-sessions
POST   /api/v1/trading-sessions
PUT    /api/v1/trading-sessions/{tradingSession}
DELETE /api/v1/trading-sessions/{tradingSession}

GET    /api/v1/market-conditions
POST   /api/v1/market-conditions
PUT    /api/v1/market-conditions/{marketCondition}
DELETE /api/v1/market-conditions/{marketCondition}

GET    /api/v1/entry-models
POST   /api/v1/entry-models
PUT    /api/v1/entry-models/{entryModel}
DELETE /api/v1/entry-models/{entryModel}

GET    /api/v1/setup-grades
POST   /api/v1/setup-grades
PUT    /api/v1/setup-grades/{setupGrade}
DELETE /api/v1/setup-grades/{setupGrade}

GET    /api/v1/mistake-categories
POST   /api/v1/mistake-categories
PUT    /api/v1/mistake-categories/{mistakeCategory}
DELETE /api/v1/mistake-categories/{mistakeCategory}

GET    /api/v1/psychology-states
POST   /api/v1/psychology-states
PUT    /api/v1/psychology-states/{psychologyState}
DELETE /api/v1/psychology-states/{psychologyState}

GET    /api/v1/tags
POST   /api/v1/tags
PUT    /api/v1/tags/{tag}
DELETE /api/v1/tags/{tag}

GET    /api/v1/trading-rules
POST   /api/v1/trading-rules
PUT    /api/v1/trading-rules/{tradingRule}
DELETE /api/v1/trading-rules/{tradingRule}
```

Request/response body = the table's own editable columns (see `DATABASE_SCHEMA.md` §5, §5.9).
`DELETE` sets `status = archived` unless `?force=true` and no trade references it, matching the
Strategy delete semantics in §6 above. List responses are unpaginated arrays (small per-user
sets) and include archived items only when `?include_archived=true` is passed (default: active
only, so dropdowns don't need client-side filtering).

---

## 8. Analytics API

Every endpoint below accepts the **common analytics query parameters**:

```text
account_id       (omit = across all accounts)
date_from, date_to
strategy_id, strategy_setup_id, symbol, asset_class, trading_session_id, market_condition_id,
entry_model_id, setup_grade_id, direction, outcome, tag_id, weekday, followed_plan
```

These are the same filters as `GET /trades` (§4), so the frontend's shared `FilterBar` component
(see `FRONTEND_ARCHITECTURE.md`) can drive both trade list and analytics pages identically.

### `GET /api/v1/analytics/overview`
Response `200`: the full SRS §22.1 metric set —
```json
{
  "data": {
    "total_trades": 140, "wins": 62, "losses": 71, "breakevens": 7,
    "win_rate": "46.9700", "loss_rate": "51.4300", "breakeven_rate": "5.0000",
    "gross_profit": "3200.0000", "gross_loss": "-1385.0000", "net_profit": "1815.0000",
    "net_profit_percent": "36.3000",
    "average_winner": "51.6100", "average_loser": "-19.5100",
    "largest_winner": "410.0000", "largest_loser": "-95.0000",
    "average_r": "1.1700", "total_r": "163.8000",
    "profit_factor": "2.3100", "expectancy_r": "0.5900", "expectancy_amount": "12.9600",
    "risk_reward_ratio": "2.6400",
    "current_win_streak": 2, "current_loss_streak": 0,
    "longest_win_streak": 6, "longest_loss_streak": 4,
    "maximum_drawdown_percent": "4.2000", "current_drawdown_percent": "1.5700",
    "recovery_factor": "4.3200",
    "average_holding_time_minutes": 96, "average_winning_hold_minutes": 82, "average_losing_hold_minutes": 114
  }
}
```
Every field's exact formula is defined in `ANALYTICS_FORMULAS.md`.

### `GET /api/v1/analytics/equity-curve`
Query adds `period` (as in accounts/growth). Response `200`: `{ "data": { "series": [{ "date", "balance", "equity", "high_water_mark" }] } }`.

### `GET /api/v1/analytics/drawdown`
Response shape matches `GET /accounts/{account}/drawdown` (§3) but supports the full filter set
above and can be account-agnostic.

### `GET /api/v1/analytics/strategies`
Response `200`: array, one row per strategy — SRS §24 table columns:
```json
{ "data": [{ "strategy": {"id","name"}, "total_trades", "wins", "losses", "win_rate", "avg_r", "net_r", "net_profit_loss", "profit_factor", "expectancy_r", "maximum_drawdown_percent", "sample_confidence": "developing" }] }
```
`sample_confidence` uses the configurable thresholds in `ANALYTICS_FORMULAS.md` §Sample Confidence.

### `GET /api/v1/analytics/pairs`
Same row shape, keyed by `symbol` instead of `strategy`, plus `best_strategy`, `best_session`
per SRS §25.

### `GET /api/v1/analytics/sessions`
Row per `trading_session`, SRS §26 metrics.

### `GET /api/v1/analytics/weekdays`
Row per weekday (`monday`..`sunday`), SRS §27 metrics.

### `GET /api/v1/analytics/setup-grades`
Row per setup grade, SRS §13 table shape.

### `GET /api/v1/analytics/mistakes`
Response `200`:
```json
{
  "data": {
    "most_common": { "mistake_category": {"id","name"}, "occurrences": 18 },
    "most_expensive": { "mistake_category": {"id","name"}, "occurrences": 18, "losses": 11, "estimated_cost_r": "-6.4000" },
    "by_category": [{ "mistake_category": {"id","name"}, "occurrences", "loss_rate", "estimated_cost_r", "estimated_cost_amount" }],
    "by_month": [{ "month": "2026-07", "occurrences": 9 }],
    "by_strategy": [{ "strategy": {"id","name"}, "occurrences" }]
  }
}
```

### `GET /api/v1/analytics/psychology`
Response `200`: win rate / net R broken down `by_emotion` (before-trade state) and aggregate
average discipline/execution/emotional-control/patience scores, per SRS §17.3.

### `GET /api/v1/analytics/risk`
Response `200`: SRS §29 fields — average risk %, highest/lowest risk trade, average risk on
wins/losses, risk by strategy, risk by setup grade, risk consistency (stddev of risk%), daily/
weekly risk used, consecutive-loss exposure, plus funded-account buffer fields when
`account_id` resolves to a funded/evaluation account.

### `GET /api/v1/analytics/rule-compliance`
Response `200`: `{ "data": { "compliant_trades_net_r": "…", "violating_trades_net_r": "…", "violations_by_rule": [...], "violations_by_month": [...] } }` per SRS §33.

### `GET /api/v1/analytics/historical-setup`
Query: the taxonomy filters from the common set are the "query" (strategy_id, symbol,
trading_session_id, setup_grade_id, higher_timeframe_bias, min_planned_rr, …) — this endpoint IS
the SRS §39 "High-Probability Historical Report" search.
Response `200`:
```json
{ "data": { "matches": 42, "wins": 26, "losses": 16, "historical_win_rate": "61.9000", "average_r": "1.8400", "profit_factor": "3.1000", "net_r": "77.3000", "sample_confidence": "reliable" } }
```
Also backs the SRS §21.3 Setup Quality Score panel when called with a specific in-progress
trade's attributes instead of stored filter IDs (`POST` variant below).

### `POST /api/v1/analytics/historical-setup/score`
Used while composing a new trade (before saving) to preview its Setup Quality Score against
history. Request: the in-progress trade's strategy/setup/session/grade/HTF-bias/checklist-checked
fields. Response `200`: `{ "data": { "setup_quality_score": "87.0000", "similar_historical_trades": 63, "historical_win_rate": "57.1000", "average_result_r": "1.9200", "profit_factor": "2.6400" } }`.

### `GET /api/v1/analytics/compare`
Query: two independent filter sets, `a[...]` and `b[...]`, each using the common analytics
filters (SRS §40). Response `200`: `{ "data": { "a": {...overview shape...}, "b": {...overview shape...} } }`.

---

## 9. Calendar API

### `GET /api/v1/calendar/month`
Query: `account_id`, `year`, `month` (SRS §80 example: `?account_id=1&year=2026&month=8`).
Response `200`:
```json
{
  "data": {
    "year": 2026, "month": 8,
    "summary": { "net_profit_loss": "420.0000", "growth_percent": "8.4000", "net_r": "18.7000", "trade_count": 32, "wins": 15, "losses": 17, "win_rate": "46.9000" },
    "days": [{ "date": "2026-08-01", "net_profit_loss": "105.0000", "net_profit_loss_percent": "2.1000", "net_r": "2.1000", "trade_count": 1, "wins": 1, "losses": 0, "breakevens": 0 }]
  }
}
```

### `GET /api/v1/calendar/day/{date}`
Query: `account_id`. Response `200`: `{ "data": { "trades": [...trade list resources...], "summary": {...same shape as a calendar day...}, "best_trade": {...}, "worst_trade": {...} } }`.

---

## 10. Reports API

### `GET /api/v1/reports/performance`
Same filter set as Analytics (§8); response = `analytics/overview` shape wrapped for
report-page presentation.

### `GET /api/v1/reports/strategies` · `GET /api/v1/reports/pairs` · `GET /api/v1/reports/mistakes` · `GET /api/v1/reports/psychology`
Thin wrappers over the equivalent `/analytics/*` endpoints — kept as distinct routes because
report pages may add report-specific formatting (e.g. include narrative text) without coupling
the Analytics pages to that concern.

### `GET /api/v1/reports/monthly`
Query: `account_id`, `year`, `month`. Response `200`: the `monthly_reviews` row for that period,
generating one on-demand (without persisting) if the scheduled job hasn't run yet for the current
month.

### `POST /api/v1/reports/export`
Request: `{ "type": "trades_csv"|"trades_excel"|"strategy_report_csv"|"monthly_report"|"account_history"|"mistakes_report"|"performance_report", "filters": {...same as analytics filters...} }`.
Response `202 Accepted`: `{ "data": { "export_id": "uuid", "status": "queued" } }` — queued via
`ExportTradesJob` (`ARCHITECTURE.md` §6); frontend polls or receives a notification.

### `GET /api/v1/reports/export/{export}`
Response `200`: `{ "data": { "export_id": "uuid", "status": "queued"|"processing"|"ready"|"failed", "download_url": "…"|null } }`.

---

## 11. Dashboard API

### `GET /api/v1/dashboard`
Query: `account_id` (omit = all accounts combined).
Response `200`: single consolidated payload (SRS §82) —
```json
{
  "data": {
    "account_summary": { "balance", "starting_balance", "net_profit_loss", "growth_percent" },
    "kpis": { "...same shape as analytics/overview, trimmed to dashboard card set..." },
    "equity_curve": { "series": [...] },
    "recent_trades": [ "...trade list resources, limit 10..." ],
    "monthly_performance": { "net_profit_loss", "net_r", "growth_percent", "trades", "win_rate", "profit_factor" },
    "best_strategy": { "id", "name", "net_r" },
    "best_pair": { "symbol", "net_r" },
    "best_session": { "id", "name", "net_r" },
    "goals": [ "...active Goal resources with progress_percent..." ],
    "drawdown_status": { "current_drawdown_percent", "maximum_drawdown_percent" },
    "calendar_preview": { "days": [ "...current month, summary fields only..." ] },
    "mistake_insight": { "most_expensive": {...} },
    "milestones": { "current_growth_percent", "next_goal_percent", "progress_percent" }
  }
}
```
This single call is what the dashboard page fetches on load, per SRS §82's stated goal of
reducing frontend request count.

---

## 12. Goals API

```text
GET    /api/v1/goals
POST   /api/v1/goals
GET    /api/v1/goals/{goal}
PUT    /api/v1/goals/{goal}
DELETE /api/v1/goals/{goal}
GET    /api/v1/goals/{goal}/progress
```
Request/response bodies mirror `goals` / `goal_progress` table fields (`DATABASE_SCHEMA.md` §8.1–8.2). `progress_percent` on the Goal resource is computed as
`(current_value - start_value) / (target_value - start_value) × 100`, clamped to `[0, 100]` for
display (raw value still returned unclamped for over-achievement cases).

---

## 13. Settings API

### `GET /api/v1/settings`
Response `200`: the authenticated user's editable preferences —
```json
{
  "data": {
    "timezone": "America/New_York", "default_currency": "USD", "theme": "system",
    "sidebar_collapsed": false,
    "setup_quality_score_weights": { "htf_alignment": 20, "checklist_completion": 35, "session_correctness": 15, "min_rr_achieved": 15, "news_avoidance": 15 },
    "sample_confidence_thresholds": { "low_sample_max": 9, "developing_max": 29, "moderate_max": 49 }
  }
}
```
`setup_quality_score_weights` and `sample_confidence_thresholds` back the configurable defaults
in `ANALYTICS_FORMULAS.md` §12 and §15 respectively — stored as a single JSONB settings blob on
`users` (see `DATABASE_SCHEMA.md` note below), not a relational table, since these are per-user
scalar preferences, not entities with their own identity/lifecycle.

### `PUT /api/v1/settings`
Request: any subset of the fields above. Response `200`: updated settings resource.
`setup_quality_score_weights` values must sum to `100` (`422` otherwise).

> **Schema note:** `DATABASE_SCHEMA.md` §2 lists `timezone`, `default_currency`, `theme` as
> individual `users` columns (frequently read, worth their own indexed columns). The remaining
> preferences (`sidebar_collapsed`, `setup_quality_score_weights`,
> `sample_confidence_thresholds`) live in a single `users.preferences JSONB NOT NULL DEFAULT
> '{}'` column, added as a Phase 1a migration alongside the other `users` extensions — flagged
> here rather than re-opening `DATABASE_SCHEMA.md` §2 since it's a one-line addition to an
> already-designed table, not a new table.

---

## 14. Search API

### `GET /api/v1/search`
Query: `q` (required), `account_id?`. Searches trade_number, symbol, strategy name, journal text
fields, mistake notes, tag names, account names, screenshot captions (SRS §41) via Postgres
full-text search (`tsvector`/`tsquery`) on a generated column, not `ILIKE '%...%'` scans.
Response `200`: `{ "data": { "trades": [...], "strategies": [...] } }` — grouped by entity type,
each item minimal (id, label, route) for a command-palette-style UI.

---

## 15. Validation error reference

Common `422` triggers, mapped to the SRS §87 rules they enforce:

| Field | Rule |
|---|---|
| `trading_account_id` | must exist and belong to the authenticated user |
| `symbol` | required, non-empty |
| `direction` | required, one of `long`,`short` |
| `planned_risk_amount`, `actual_risk_amount`, `risk_percent` | numeric, ≥ 0 |
| `strategy_id` | must belong to the authenticated user |
| `strategy_setup_id` | must belong to the given `strategy_id` |
| `exit_at` | must be ≥ `entry_at` when both present |
| screenshot `file` | mime in `image/jpeg,image/png,image/webp`; size ≤ configured max |
| `*_score` fields | integer within their documented range (1–5 or 1–10) |
