# TradeGrowth — Database Schema

**Engine:** PostgreSQL 16+
**Status:** Design document — no migrations exist yet. This is the contract migrations must follow.
**Scope:** Single-user-per-account-tree ownership model, built so multi-user SaaS conversion later
requires no schema surgery (every domain table is reachable from `users` via a direct or indirect
`user_id`).

---

## 0. Conventions used throughout this document

- **Table names:** `snake_case`, plural.
- **Primary keys:** `id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY` (Laravel `id()`), plus a
  `uuid UUID NOT NULL DEFAULT gen_random_uuid()` with a unique index on every table that is
  referenced from a URL or an external upload path (trades, trading_accounts, strategies,
  screenshots, goals, reports). Internal-only pivot/lookup tables skip the UUID.
- **Timestamps:** `created_at`, `updated_at` as `TIMESTAMPTZ`, stored UTC (SRS §89). Tables that
  are immutable snapshots (`trade_checklist_snapshots`, `account_balance_snapshots`,
  `analytics_snapshots`, `goal_progress`, `audit_logs`) have `created_at` only — they are never
  updated in place.
- **Soft deletes:** `deleted_at TIMESTAMPTZ NULL` where SRS requires history preservation
  (trades, strategies, trading_accounts). See §12 for the general delete-safety policy.
- **Money:** `DECIMAL(20,4)`. **Prices:** `DECIMAL(20,8)` (crypto/forex need sub-pip precision).
  **Percentages / R-multiples / scores:** `DECIMAL(10,4)`. Never `FLOAT`/`DOUBLE` for anything
  financial (SRS §88).
- **Enums:** implemented as Postgres native `ENUM` types (`CREATE TYPE ... AS ENUM (...)`) rather
  than free-text `VARCHAR`, since the engine is fixed to Postgres. Each enum type is named
  `<table>_<column>_enum`. Values are listed inline per table below.
- **FK naming:** `<singular_table>_id`, e.g. `trading_account_id`, `strategy_id`.
- **Ownership root:** `users.id`. Every table below states its **ownership path** — how it chains
  back to a `user_id`, either directly or via a parent FK — because authorization Policies key off
  this chain (see `ARCHITECTURE.md` §Security).

---

## 1. Domain grouping (ER overview)

```text
SYSTEM
  users ─┬─ personal_access_tokens (Sanctum)
         └─ audit_logs

ACCOUNTS
  users ─< trading_accounts ─┬─< account_transactions
                              ├─< account_balance_snapshots
                              └─< analytics_snapshots

TAXONOMY (per-user, user-managed lookup lists)
  users ─< strategies ─┬─< strategy_setups
                        ├─< strategy_rules
                        └─< strategy_checklist_items
  users ─< trading_sessions
  users ─< market_conditions
  users ─< entry_models
  users ─< setup_grades
  users ─< mistake_categories
  users ─< psychology_states
  users ─< tags
  users ─< trading_rules

TRADES (the core record)
  trading_accounts ─< trades ─┬─< trade_partial_exits
                                ├─< screenshots (polymorphic: trade | strategy)
                                ├─< trade_journals (1:1)
                                ├─< trade_checklist_snapshots
                                ├─< trade_mistakes >─ mistake_categories
                                ├─< trade_psychology (1:1) >─ psychology_states
                                ├─< trade_rule_violations >─ trading_rules
                                └─< trade_tag >─ tags

REPORTING / GOALS
  users ─< goals ─< goal_progress
  users ─< monthly_reviews
```

---

## 2. `users`

Ownership path: root.

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| name | varchar(255) | no | | |
| email | varchar(255) | no | | unique index |
| email_verified_at | timestamptz | yes | null | |
| password | varchar(255) | no | | hashed |
| timezone | varchar(64) | no | `'UTC'` | IANA tz name, e.g. `America/New_York` |
| default_currency | char(3) | no | `'USD'` | ISO 4217 |
| theme | `users_theme_enum` (`light`,`dark`,`system`) | no | `'system'` | |
| preferences | jsonb | no | `'{}'` | small scalar-preference blob: `sidebar_collapsed`, `setup_quality_score_weights`, `sample_confidence_thresholds` — see `API_CONTRACTS.md` §13. Not a table because these values have no independent identity/lifecycle of their own. |
| remember_token | varchar(100) | yes | null | Laravel default |
| created_at / updated_at | timestamptz | no | now() | |

Relationships: `hasMany` on every taxonomy/account table below; `hasManyThrough` on `trades` via
`trading_accounts`.

Also standard Laravel/Sanctum tables kept as-is (not redesigned here): `password_reset_tokens`,
`sessions` (Laravel's own — this is why trading sessions are renamed, see §12.1), `cache`,
`cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `personal_access_tokens`.

---

## 3. Accounts domain

### 3.1 `trading_accounts`

Ownership path: `user_id` (direct).

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| uuid | uuid | no | gen_random_uuid() | unique |
| user_id | bigint FK→users.id | no | | ON DELETE CASCADE |
| name | varchar(255) | no | | |
| account_type | `trading_accounts_account_type_enum` (`personal_live`,`demo`,`funded`,`prop_evaluation`,`prop_funded`,`backtesting`,`custom`) | no | | |
| broker | varchar(255) | yes | null | |
| currency | char(3) | no | | ISO 4217 |
| initial_balance | decimal(20,4) | no | | |
| current_balance | decimal(20,4) | no | | denormalized running balance, recalculated server-side on every trade/transaction write — never trusted from client |
| current_equity | decimal(20,4) | no | | balance ± open-position floating P&L; V1 has no live open-position feed, so equals `current_balance` until broker sync exists |
| status | `trading_accounts_status_enum` (`active`,`archived`,`closed`) | no | `'active'` | |
| max_overall_drawdown_percent | decimal(10,4) | yes | null | funded-account only |
| max_daily_drawdown_percent | decimal(10,4) | yes | null | funded-account only |
| profit_target_percent | decimal(10,4) | yes | null | funded-account only |
| minimum_trading_days | smallint | yes | null | |
| maximum_trading_days | smallint | yes | null | |
| payout_target | decimal(20,4) | yes | null | |
| consistency_rule_percent | decimal(10,4) | yes | null | |
| drawdown_calculation_type | `trading_accounts_dd_calc_enum` (`balance_based`,`equity_based`,`trailing`) | yes | null | |
| daily_reset_time | time | yes | null | broker's daily-reset clock time, paired with `daily_reset_timezone` |
| daily_reset_timezone | varchar(64) | yes | null | IANA tz; required for accurate daily-DD-buffer math (SRS §89) |
| challenge_phase | varchar(64) | yes | null | free text, e.g. "Phase 1", "Verification" |
| account_created_date | date | no | | trader-entered account opening date, distinct from `created_at` (row insert time) |
| notes | text | yes | null | |
| created_at / updated_at | timestamptz | no | now() | |
| deleted_at | timestamptz | yes | null | soft delete — never hard-delete an account with trades |

Indexes: `(user_id, status)`.

Relationships: `belongsTo(User)`; `hasMany(AccountTransaction)`, `hasMany(AccountBalanceSnapshot)`,
`hasMany(Trade)`, `hasMany(Goal)`, `hasMany(AnalyticsSnapshot)`.

### 3.2 `account_transactions`

Ownership path: `trading_account_id → user_id`.

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| trading_account_id | bigint FK→trading_accounts.id | no | | ON DELETE CASCADE |
| type | `account_transactions_type_enum` (`deposit`,`withdrawal`,`fee`,`refund`,`profit_split`,`adjustment`) | no | | |
| amount | decimal(20,4) | no | | positive; sign/effect on balance derived from `type` in application logic (deposit/refund add, withdrawal/fee/profit_split subtract, adjustment can be ±) |
| transaction_date | date | no | | |
| notes | text | yes | null | |
| created_at / updated_at | timestamptz | no | now() | |

Indexes: `(trading_account_id, transaction_date)`.

Relationships: `belongsTo(TradingAccount)`.

**Design decision:** these rows are explicitly excluded from trading P&L calculations everywhere
(SRS §8.3, §109) — the Analytics layer only sums `trades.net_profit_loss`; `account_transactions`
only feed the *balance* reconciliation and Growth % formula's "Net Deposits" term (see
`ANALYTICS_FORMULAS.md`).

### 3.3 `account_balance_snapshots` *(schema not detailed in SRS — designed here)*

Purpose: a persisted daily point-in-time balance/equity/drawdown record per account, so the
equity curve and drawdown charts render from O(days) rows instead of replaying every trade and
transaction on every request, and so the scheduler's daily job (SRS §86) has somewhere to write.

Ownership path: `trading_account_id → user_id`.

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| trading_account_id | bigint FK→trading_accounts.id | no | | ON DELETE CASCADE |
| snapshot_date | date | no | | the trading day this row represents |
| balance | decimal(20,4) | no | | closing balance as of end of `snapshot_date` |
| equity | decimal(20,4) | no | | |
| high_water_mark | decimal(20,4) | no | | running max(balance) up to and including this date |
| drawdown_percent | decimal(10,4) | no | `0` | see Drawdown formula |
| net_deposits_to_date | decimal(20,4) | no | `0` | cumulative deposits − withdrawals up to this date, for Growth % |
| trade_count | integer | no | `0` | trades closed on this date, denormalized for calendar/day-summary reuse |
| source | `account_balance_snapshots_source_enum` (`trade_close`,`scheduled`,`manual_adjustment`) | no | `'scheduled'` | what produced this row |
| created_at | timestamptz | no | now() | write-once |

Indexes: unique `(trading_account_id, snapshot_date)`; `(trading_account_id, snapshot_date DESC)`
for equity-curve range queries.

Relationships: `belongsTo(TradingAccount)`.

A new row is written (or the day's existing row upserted) every time a trade on that account
closes, and once nightly by the scheduler to backfill no-trade days so the equity curve has no
gaps.

---

## 4. Trades domain

### 4.1 `trades`

Ownership path: `trading_account_id → user_id`, **plus** a denormalized `user_id` directly on the
row (see Decision #10 below) purely for query ergonomics — `trading_account_id` remains the
single source of truth for ownership.

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| uuid | uuid | no | gen_random_uuid() | unique |
| user_id | bigint FK→users.id | no | | ON DELETE CASCADE; **denormalized**, must always equal `trading_account.user_id` — enforced in the Trade Action/Service layer, not by trigger, to keep write logic in one place |
| trading_account_id | bigint FK→trading_accounts.id | no | | ON DELETE RESTRICT (soft-delete the account instead; see §12) |
| trade_number | integer | no | | per-account sequential display number, assigned by the service layer on creation (not a DB sequence, since it must skip nothing visible and stay stable) |
| trade_date | date | no | | the trading calendar day this trade belongs to (drives calendar aggregation) |
| entry_at | timestamptz | yes | null | null while `status = planned` |
| exit_at | timestamptz | yes | null | null until closed; CHECK `exit_at >= entry_at` when both set |
| asset_class | `trades_asset_class_enum` (`forex`,`crypto`,`indices`,`commodities`,`stocks`,`futures`,`custom`) | no | | |
| symbol | varchar(32) | no | | canonical symbol, e.g. `EURUSD`, `XAUUSD`, `BTCUSD` |
| broker_symbol | varchar(32) | yes | null | only set when it differs from `symbol` — see Decision #5 |
| direction | `trades_direction_enum` (`long`,`short`) | no | | |
| planned_entry_price | decimal(20,8) | yes | null | |
| actual_entry_price | decimal(20,8) | yes | null | required once `status != planned` |
| stop_loss_price | decimal(20,8) | yes | null | |
| planned_take_profit_price | decimal(20,8) | yes | null | |
| actual_exit_price | decimal(20,8) | yes | null | null if still open; for multi-partial-exit trades this is the *final* exit price, partials live in `trade_partial_exits` |
| position_size | decimal(20,8) | yes | null | lot size / units, asset-class dependent |
| quantity | decimal(20,8) | yes | null | contracts/shares where distinct from `position_size` |
| contract_size | decimal(20,8) | yes | null | |
| leverage | decimal(10,2) | yes | null | |
| tick_pip_value | decimal(20,8) | yes | null | |
| planned_risk_amount | decimal(20,4) | yes | null | |
| actual_risk_amount | decimal(20,4) | yes | null | denominator for R-multiple — see ANALYTICS_FORMULAS.md |
| risk_percent | decimal(10,4) | yes | null | actual risk / account balance at entry time, ×100 |
| gross_profit_loss | decimal(20,4) | yes | null | before fees |
| fees | decimal(20,4) | no | `0` | |
| commission | decimal(20,4) | no | `0` | |
| swap | decimal(20,4) | no | `0` | |
| net_profit_loss | decimal(20,4) | yes | null | `gross_profit_loss - fees - commission - swap`, recalculated server-side on save |
| net_profit_loss_percent | decimal(10,4) | yes | null | net P&L / account balance at entry time |
| planned_rr | decimal(10,4) | yes | null | |
| achieved_rr | decimal(10,4) | yes | null | |
| r_multiple | decimal(10,4) | yes | null | `net_profit_loss / actual_risk_amount`, recalculated server-side |
| outcome | `trades_outcome_enum` (`win`,`loss`,`breakeven`) | yes | null | null until closed |
| strategy_id | bigint FK→strategies.id | yes | null | ON DELETE RESTRICT |
| strategy_setup_id | bigint FK→strategy_setups.id | yes | null | ON DELETE RESTRICT; app validates it belongs to `strategy_id` (SRS §87) |
| trading_session_id | bigint FK→trading_sessions.id | yes | null | ON DELETE RESTRICT — renamed from SRS's "session_id", see Decision #1 |
| market_condition_id | bigint FK→market_conditions.id | yes | null | ON DELETE RESTRICT |
| entry_model_id | bigint FK→entry_models.id | yes | null | ON DELETE RESTRICT |
| setup_grade_id | bigint FK→setup_grades.id | yes | null | ON DELETE RESTRICT |
| timeframe | varchar(16) | yes | null | e.g. `M15`, `H1`, `D1` |
| higher_timeframe_bias | `trades_htf_bias_enum` (`bullish`,`bearish`,`neutral`,`mixed`) | yes | null | |
| status | `trades_status_enum` (`planned`,`open`,`closed`,`cancelled`,`invalidated`) | no | `'planned'` | |
| followed_plan | boolean | yes | null | |
| would_take_again | boolean | yes | null | |
| execution_score | smallint | yes | null | 1–10, CHECK between 1 and 10 |
| discipline_score | smallint | yes | null | 1–10 |
| patience_score | smallint | yes | null | 1–10 |
| emotional_control_score | smallint | yes | null | 1–10 |
| setup_quality_score | decimal(10,4) | yes | null | 0–100, computed at save time per ANALYTICS_FORMULAS.md §Setup Quality Score, stored for fast historical-match querying |
| created_at / updated_at | timestamptz | no | now() | |
| deleted_at | timestamptz | yes | null | soft delete |

CHECK constraints: `outcome IS NULL OR status = 'closed'`; `execution_score BETWEEN 1 AND 10` (and
same for the other three 1–10 scores).

Indexes:
- `(trading_account_id, trade_date)` — calendar & date-range queries.
- `(user_id, strategy_id)` — strategy analytics.
- `(user_id, symbol)` — pair analytics.
- `(user_id, trading_session_id)` — session analytics.
- `(user_id, status, outcome)` — dashboard KPI queries.
- unique `(trading_account_id, trade_number)`.
- GIN index is not needed (no JSONB on this table).

Relationships: `belongsTo(User)`, `belongsTo(TradingAccount)`, `belongsTo(Strategy)`,
`belongsTo(StrategySetup)`, `belongsTo(TradingSession)`, `belongsTo(MarketCondition)`,
`belongsTo(EntryModel)`, `belongsTo(SetupGrade)`; `hasMany(TradePartialExit)`,
`morphMany(Screenshot)`, `hasOne(TradeJournal)`, `hasMany(TradeChecklistSnapshot)`,
`hasMany(TradeMistake)`, `hasOne(TradePsychology)`, `hasMany(TradeRuleViolation)`,
`belongsToMany(Tag, 'trade_tag')`.

### 4.2 `trade_partial_exits`

Ownership path: `trade_id → trading_account_id → user_id`.

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| trade_id | bigint FK→trades.id | no | | ON DELETE CASCADE |
| exit_price | decimal(20,8) | no | | |
| quantity | decimal(20,8) | no | | |
| percentage_closed | decimal(10,4) | no | | 0–100 |
| profit_loss | decimal(20,4) | no | | |
| r_multiple | decimal(10,4) | yes | null | |
| exited_at | timestamptz | no | | |
| notes | text | yes | null | |
| created_at / updated_at | timestamptz | no | now() | |

Indexes: `(trade_id, exited_at)`.

Relationships: `belongsTo(Trade)`.

### 4.3 `screenshots` *(generalized from SRS's `trade_screenshots` — see Decision #4)*

Ownership path: polymorphic parent → `user_id` (trade → trading_account → user, or strategy →
user).

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| uuid | uuid | no | gen_random_uuid() | unique; used to build signed/private URLs, never expose `id` or `storage_path` directly (SRS §95) |
| screenshotable_type | varchar(255) | no | | Laravel morph type, `Trade` \| `Strategy` |
| screenshotable_id | bigint | no | | |
| type | `screenshots_type_enum` (`before`,`during`,`after`,`example`) | no | | `example` is strategy-only |
| storage_disk | varchar(64) | no | | `local` (dev) or `s3`/`r2` (prod), via Laravel Storage abstraction |
| storage_path | varchar(512) | no | | randomized filename (SRS §93/§94), never derived from user input |
| mime_type | varchar(128) | no | | validated against allow-list: jpeg, png, webp |
| file_size_bytes | integer | no | | validated ≤ configured max (10 MB default, SRS §94) |
| caption | varchar(500) | yes | null | |
| timeframe | varchar(16) | yes | null | |
| annotation_note | text | yes | null | |
| sort_order | integer | no | `0` | |
| created_at / updated_at | timestamptz | no | now() | |

Indexes: `(screenshotable_type, screenshotable_id, sort_order)`.

Relationships: `morphTo()`; consumers are `Trade::screenshots()` (`morphMany`, filtered by
`type IN (before,during,after)`) and `Strategy::exampleScreenshots()` (`morphMany`, filtered by
`type = example`).

### 4.4 `trade_journals`

Ownership path: `trade_id → trading_account_id → user_id`. 1:1 with `trades`.

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| trade_id | bigint FK→trades.id | no | | unique, ON DELETE CASCADE |
| before_trade_reason | text | yes | null | |
| htf_bias_notes | text | yes | null | |
| liquidity_target | text | yes | null | |
| setup_invalidation | text | yes | null | |
| entry_reason | text | yes | null | |
| planned_management | text | yes | null | |
| confidence_score | smallint | yes | null | 1–10, before-trade |
| during_trade_notes | text | yes | null | |
| market_conditions_changed | boolean | yes | null | |
| plan_altered | boolean | yes | null | |
| plan_altered_reason | text | yes | null | |
| after_trade_summary | text | yes | null | |
| what_went_well | text | yes | null | |
| what_went_wrong | text | yes | null | |
| lesson_learned | text | yes | null | |
| next_time_improvement | text | yes | null | |
| monthly_review_id | bigint FK→monthly_reviews.id | yes | null | ON DELETE SET NULL; back-reference once a monthly review cites this trade as best/worst |
| created_at / updated_at | timestamptz | no | now() | |

Relationships: `belongsTo(Trade)`, `belongsTo(MonthlyReview)`.

**Note:** journal-completeness scoring (SRS §104) is computed, not stored — it reads which of
these fields plus screenshot/mistake/psychology presence are non-null. No dedicated column.

---

## 5. Taxonomy domain (user-managed lookup lists)

All six lookup tables below (`trading_sessions`, `market_conditions`, `entry_models`,
`setup_grades`, `mistake_categories`, `psychology_states`) share one shape, seeded per-user at
registration from the SRS §112 defaults, editable/archivable/deletable by that user without
touching anyone else's rows. This directly satisfies "Strategies must be fully user-managed"
(§10) generalized to every taxonomy concept, and sidesteps a shared-global-row permission model
that would be needed for a later multi-user SaaS conversion.

### 5.1 `trading_sessions` *(renamed from SRS's `sessions` — Decision #1; schema designed here — Decision #2)*

Ownership path: `user_id` (direct).

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| user_id | bigint FK→users.id | no | | ON DELETE CASCADE |
| name | varchar(100) | no | | e.g. `Asia`, `London`, `New York`, `London/New York Overlap` |
| description | text | yes | null | |
| start_time_utc | time | yes | null | optional, enables future auto-session-detection from `entry_at` |
| end_time_utc | time | yes | null | |
| status | `taxonomy_status_enum` (`active`,`archived`) | no | `'active'` | shared enum type reused by all six taxonomy tables |
| sort_order | integer | no | `0` | |
| created_at / updated_at | timestamptz | no | now() | |

Indexes: unique `(user_id, name)`.

Relationships: `belongsTo(User)`, `hasMany(Trade)` (as `trading_session_id`).

### 5.2 `market_conditions` *(schema designed here — Decision #2)*

Same shape as §5.1 minus the time columns. Seed values: Trending, Ranging, Reversal,
Consolidation, High Volatility, Low Volatility, News-driven, Custom.

### 5.3 `entry_models` *(schema designed here — Decision #2)*

Same shape as §5.1 minus the time columns. Seed values: FVG, Order Block, Breaker, Retest,
Fibonacci, Market Structure Shift, Support/Resistance, Custom.

### 5.4 `setup_grades`

Ownership path: `user_id` (direct).

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| user_id | bigint FK→users.id | no | | ON DELETE CASCADE |
| name | varchar(50) | no | | e.g. `A+`, `A`, `B`, `C` |
| description | text | yes | null | grade definition criteria, free text |
| score_min | decimal(10,4) | yes | null | for mapping to setup_quality_score ranges, optional |
| score_max | decimal(10,4) | yes | null | |
| sort_order | integer | no | `0` | best-to-worst display order |
| status | `taxonomy_status_enum` | no | `'active'` | |
| created_at / updated_at | timestamptz | no | now() | |

Indexes: unique `(user_id, name)`.

### 5.5 `mistake_categories`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| user_id | bigint FK→users.id | no | | ON DELETE CASCADE |
| name | varchar(150) | no | | |
| description | text | yes | null | |
| severity_default | `mistake_categories_severity_enum` (`low`,`medium`,`high`,`critical`) | no | `'medium'` | |
| status | `taxonomy_status_enum` | no | `'active'` | |
| created_at / updated_at | timestamptz | no | now() | |

Indexes: unique `(user_id, name)`.

Relationships: `hasMany(TradeMistake)`.

### 5.6 `psychology_states`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| user_id | bigint FK→users.id | no | | ON DELETE CASCADE |
| name | varchar(100) | no | | e.g. `Calm`, `FOMO`, `Revenge` |
| status | `taxonomy_status_enum` | no | `'active'` | |
| created_at / updated_at | timestamptz | no | now() | |

Indexes: unique `(user_id, name)`.

Relationships: `hasMany(TradePsychology, 'before_psychology_state_id')`.

### 5.7 `tags`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| user_id | bigint FK→users.id | no | | ON DELETE CASCADE |
| name | varchar(100) | no | | |
| slug | varchar(120) | no | | url-safe, generated from name |
| created_at / updated_at | timestamptz | no | now() | |

Indexes: unique `(user_id, slug)`.

Relationships: `belongsToMany(Trade, 'trade_tag')`.

### 5.8 `trade_tag` (pivot)

| Column | Type | Null | Notes |
|---|---|---|---|
| trade_id | bigint FK→trades.id | no | ON DELETE CASCADE |
| tag_id | bigint FK→tags.id | no | ON DELETE CASCADE |
| created_at | timestamptz | no | |

Primary key: composite `(trade_id, tag_id)`.

### 5.9 `trading_rules`

Ownership path: `user_id` (direct).

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| user_id | bigint FK→users.id | no | | ON DELETE CASCADE |
| name | varchar(255) | no | | |
| description | text | yes | null | |
| category | varchar(100) | yes | null | free text grouping, e.g. "Risk", "Session" |
| severity | `trading_rules_severity_enum` (`low`,`medium`,`high`,`critical`) | no | `'medium'` | |
| is_active | boolean | no | `true` | |
| created_at / updated_at | timestamptz | no | now() | |

Relationships: `hasMany(TradeRuleViolation)`.

---

## 6. Journaling detail domain

### 6.1 `trade_mistakes`

Ownership path: `trade_id → trading_account_id → user_id`.

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| trade_id | bigint FK→trades.id | no | | ON DELETE CASCADE |
| mistake_category_id | bigint FK→mistake_categories.id | no | | ON DELETE RESTRICT |
| severity | `mistake_categories_severity_enum` | no | | copied from category default at creation, editable per-instance |
| estimated_cost_r | decimal(10,4) | yes | null | |
| estimated_cost_amount | decimal(20,4) | yes | null | |
| notes | text | yes | null | |
| created_at / updated_at | timestamptz | no | now() | |

Indexes: `(mistake_category_id)`, `(trade_id)`.

Relationships: `belongsTo(Trade)`, `belongsTo(MistakeCategory)`.

### 6.2 `trade_psychology`

Ownership path: `trade_id → trading_account_id → user_id`. 1:1 with `trades`.

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| trade_id | bigint FK→trades.id | no | | unique, ON DELETE CASCADE |
| before_psychology_state_id | bigint FK→psychology_states.id | yes | null | ON DELETE RESTRICT — renamed for naming consistency, see Decision #6 |
| confidence_score | smallint | yes | null | 1–5 |
| focus_score | smallint | yes | null | 1–5 |
| energy_score | smallint | yes | null | 1–5 |
| stress_score | smallint | yes | null | 1–5 |
| discipline_score | smallint | yes | null | 1–10 (duplicated from `trades` for psychology-specific reporting convenience — the canonical after-trade scores live on `trades`; this mirrors SRS §65 which lists them here too) |
| execution_score | smallint | yes | null | 1–10 |
| emotional_control_score | smallint | yes | null | 1–10 |
| patience_score | smallint | yes | null | 1–10 |
| notes | text | yes | null | |
| created_at / updated_at | timestamptz | no | now() | |

CHECK: the four 1–5 scores `BETWEEN 1 AND 5`; the four 1–10 scores `BETWEEN 1 AND 10`.

Relationships: `belongsTo(Trade)`, `belongsTo(PsychologyState, 'before_psychology_state_id')`.

> **Note on duplication with `trades.execution_score` etc.:** SRS §54 and §65 both list the four
> 1–10 scores. Rather than silently pick one, this design keeps `trades.*_score` as the
> authoritative values used by all analytics queries (indexed, no join needed), and treats
> `trade_psychology.*_score` as write-mirrored by the same service-layer save operation, kept only
> for psychology-report symmetry. If this proves redundant during implementation, drop the four
> columns from `trade_psychology` and have `PsychologyAnalytics` join `trades` instead — flagged
> here so it isn't forgotten.

### 6.3 `trade_rule_violations`

Ownership path: `trade_id → trading_account_id → user_id`.

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| trade_id | bigint FK→trades.id | no | | ON DELETE CASCADE |
| trading_rule_id | bigint FK→trading_rules.id | no | | ON DELETE RESTRICT |
| notes | text | yes | null | |
| estimated_cost_r | decimal(10,4) | yes | null | |
| created_at | timestamptz | no | now() | |

Indexes: `(trading_rule_id)`, `(trade_id)`.

---

## 7. Playbook domain

### 7.1 `strategies`

Ownership path: `user_id` (direct).

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| uuid | uuid | no | gen_random_uuid() | unique |
| user_id | bigint FK→users.id | no | | ON DELETE CASCADE |
| name | varchar(255) | no | | |
| slug | varchar(280) | no | | url-safe |
| description | text | yes | null | |
| status | `strategies_status_enum` (`active`,`archived`) | no | `'active'` | |
| preferred_markets | text[] | yes | null | Postgres array of `trades_asset_class_enum` values |
| preferred_pairs | text[] | yes | null | array of symbol strings |
| preferred_sessions | bigint[] | yes | null | array of `trading_sessions.id` — kept as a simple array rather than a pivot table since it's a lightweight preference list, not a queried relationship |
| preferred_timeframes | text[] | yes | null | |
| minimum_rr | decimal(10,4) | yes | null | |
| maximum_risk_percent | decimal(10,4) | yes | null | |
| required_confirmations | text | yes | null | |
| invalidation_conditions | text | yes | null | |
| entry_model_notes | text | yes | null | |
| trade_management_rules | text | yes | null | |
| notes | text | yes | null | |
| created_at / updated_at | timestamptz | no | now() | |
| deleted_at | timestamptz | yes | null | soft delete; app enforces "only unused strategies may be hard-deleted" (SRS §10) before allowing `forceDelete` |

Indexes: unique `(user_id, slug)`.

Relationships: `belongsTo(User)`; `hasMany(StrategySetup)`, `hasMany(StrategyRule)`,
`hasMany(StrategyChecklistItem)`, `hasMany(Trade)`, `morphMany(Screenshot, type=example)`.

### 7.2 `strategy_setups`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| strategy_id | bigint FK→strategies.id | no | | ON DELETE CASCADE |
| name | varchar(255) | no | | |
| description | text | yes | null | |
| status | `taxonomy_status_enum` | no | `'active'` | |
| created_at / updated_at | timestamptz | no | now() | |

Indexes: `(strategy_id)`.

Relationships: `belongsTo(Strategy)`, `hasMany(Trade)`.

### 7.3 `strategy_rules` *(implied by SRS §50 entity list & §10 "Add rules"; schema designed here)*

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| strategy_id | bigint FK→strategies.id | no | | ON DELETE CASCADE |
| text | varchar(500) | no | | |
| sort_order | integer | no | `0` | |
| created_at / updated_at | timestamptz | no | now() | |

Indexes: `(strategy_id, sort_order)`.

### 7.4 `strategy_checklist_items`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| strategy_id | bigint FK→strategies.id | no | | ON DELETE CASCADE |
| label | varchar(255) | no | | |
| description | text | yes | null | |
| weight | decimal(10,4) | no | `1` | contributes to setup_quality_score |
| is_required | boolean | no | `false` | |
| sort_order | integer | no | `0` | |
| created_at / updated_at | timestamptz | no | now() | |

Indexes: `(strategy_id, sort_order)`.

Relationships: `belongsTo(Strategy)`, `hasMany(TradeChecklistSnapshot)`.

### 7.5 `trade_checklist_snapshots`

Ownership path: `trade_id → trading_account_id → user_id`. Immutable once written (SRS §20.3,
§109) — no `updated_at`.

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| trade_id | bigint FK→trades.id | no | | ON DELETE CASCADE |
| strategy_checklist_item_id | bigint FK→strategy_checklist_items.id | yes | null | ON DELETE SET NULL — the source item may later be edited/deleted, but the snapshot text below must survive unchanged |
| label_snapshot | varchar(255) | no | | copied at trade-save time |
| weight_snapshot | decimal(10,4) | no | | copied at trade-save time |
| is_required_snapshot | boolean | no | | copied at trade-save time |
| is_checked | boolean | no | `false` | |
| created_at | timestamptz | no | now() | |

Indexes: `(trade_id)`.

---

## 8. Reporting & goals domain

### 8.1 `goals`

Ownership path: `user_id` (direct), optionally scoped to one `trading_account_id`.

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| uuid | uuid | no | gen_random_uuid() | unique |
| user_id | bigint FK→users.id | no | | ON DELETE CASCADE |
| trading_account_id | bigint FK→trading_accounts.id | yes | null | ON DELETE CASCADE; null = applies across all accounts |
| name | varchar(255) | no | | |
| goal_type | `goals_goal_type_enum` (`account_balance`,`growth_percent`,`net_r`,`max_drawdown`,`discipline_score`,`monthly_profit`,`weekly_profit`,`min_a_plus_trades`,`max_rule_violations`) | no | | |
| target_value | decimal(20,4) | no | | |
| start_value | decimal(20,4) | no | `0` | |
| current_value | decimal(20,4) | no | `0` | latest known value, denormalized from most recent `goal_progress` row |
| start_date | date | no | | |
| target_date | date | yes | null | |
| status | `goals_status_enum` (`active`,`achieved`,`failed`,`archived`) | no | `'active'` | |
| created_at / updated_at | timestamptz | no | now() | |

Indexes: `(user_id, status)`.

Relationships: `belongsTo(User)`, `belongsTo(TradingAccount)`, `hasMany(GoalProgress)`.

### 8.2 `goal_progress` *(schema not detailed in SRS — designed here, Decision #3)*

Purpose: time-series history behind a goal's progress chart; `goals.current_value` is just the
latest entry, denormalized for fast list rendering.

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| goal_id | bigint FK→goals.id | no | | ON DELETE CASCADE |
| value | decimal(20,4) | no | | |
| recorded_at | timestamptz | no | now() | |
| note | varchar(500) | yes | null | |
| created_at | timestamptz | no | now() | |

Indexes: `(goal_id, recorded_at)`.

### 8.3 `monthly_reviews`

Ownership path: `user_id` (direct) scoped to `trading_account_id`.

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| user_id | bigint FK→users.id | no | | ON DELETE CASCADE |
| trading_account_id | bigint FK→trading_accounts.id | no | | ON DELETE CASCADE |
| year | smallint | no | | |
| month | smallint | no | | 1–12 |
| starting_balance | decimal(20,4) | no | | |
| ending_balance | decimal(20,4) | no | | |
| net_profit_loss | decimal(20,4) | no | | |
| growth_percent | decimal(10,4) | no | | |
| net_r | decimal(10,4) | no | | |
| trade_count | integer | no | | |
| wins | integer | no | | |
| losses | integer | no | | |
| breakevens | integer | no | | |
| win_rate | decimal(10,4) | no | | |
| profit_factor | decimal(10,4) | yes | null | null when gross loss is zero (undefined) |
| best_strategy_id | bigint FK→strategies.id | yes | null | ON DELETE SET NULL |
| worst_strategy_id | bigint FK→strategies.id | yes | null | ON DELETE SET NULL |
| key_lessons | text | yes | null | auto-drafted, user-editable |
| next_month_focus | text | yes | null | |
| manual_notes | text | yes | null | free-form reflection (SRS §38) |
| created_at / updated_at | timestamptz | no | now() | |

Indexes: unique `(trading_account_id, year, month)`.

### 8.4 `analytics_snapshots` *(schema not detailed in SRS — designed here, Decision #3)*

Purpose: persisted periodic aggregates (distinct from Redis's ephemeral request-scoped cache
described in `ARCHITECTURE.md`) so trend charts ("am I improving month by month?", SRS §118) don't
require recomputing history, and so the daily/weekly scheduler jobs (§86) have a durable output.

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| user_id | bigint FK→users.id | no | | ON DELETE CASCADE |
| trading_account_id | bigint FK→trading_accounts.id | no | | ON DELETE CASCADE |
| period_type | `analytics_snapshots_period_type_enum` (`daily`,`weekly`,`monthly`) | no | | |
| period_start | date | no | | |
| period_end | date | no | | |
| metrics | jsonb | no | | `{win_rate, profit_factor, expectancy, net_r, net_pnl, trade_count, avg_r, ...}` — see ANALYTICS_FORMULAS.md for the exact key set; JSONB chosen because the metric set will grow and this table is read wholesale (never queried by individual metric value) |
| created_at | timestamptz | no | now() | |

Indexes: unique `(trading_account_id, period_type, period_start)`; GIN index on `metrics` is
optional, added only if a future feature needs to filter by a metric value directly.

---

## 9. System domain

### 9.1 `audit_logs`

Ownership path: `user_id` (direct). Polymorphic target.

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint identity | no | | PK |
| user_id | bigint FK→users.id | no | | ON DELETE CASCADE |
| event | varchar(100) | no | | e.g. `trade.created`, `trade.deleted`, `account.balance_adjusted` |
| auditable_type | varchar(255) | no | | Laravel morph type |
| auditable_id | bigint | no | | |
| old_values | jsonb | yes | null | |
| new_values | jsonb | yes | null | |
| ip_address | inet | yes | null | Postgres native `inet` type |
| created_at | timestamptz | no | now() | |

Indexes: `(auditable_type, auditable_id)`, `(user_id, created_at)`.

---

## 10. Full Eloquent relationship map

```text
User
 ├─ hasMany TradingAccount
 ├─ hasMany Strategy
 ├─ hasMany TradingSession, MarketCondition, EntryModel, SetupGrade,
 │           MistakeCategory, PsychologyState, Tag, TradingRule
 ├─ hasMany Trade (denormalized direct FK)
 ├─ hasMany Goal
 ├─ hasMany MonthlyReview
 └─ hasMany AuditLog

TradingAccount
 ├─ belongsTo User
 ├─ hasMany AccountTransaction
 ├─ hasMany AccountBalanceSnapshot
 ├─ hasMany AnalyticsSnapshot
 ├─ hasMany Trade
 └─ hasMany Goal

Strategy
 ├─ belongsTo User
 ├─ hasMany StrategySetup
 ├─ hasMany StrategyRule
 ├─ hasMany StrategyChecklistItem
 ├─ hasMany Trade
 └─ morphMany Screenshot (type=example)

StrategySetup   belongsTo Strategy · hasMany Trade
StrategyChecklistItem  belongsTo Strategy · hasMany TradeChecklistSnapshot

Trade
 ├─ belongsTo User, TradingAccount, Strategy, StrategySetup, TradingSession,
 │             MarketCondition, EntryModel, SetupGrade
 ├─ hasMany TradePartialExit
 ├─ morphMany Screenshot (type in before|during|after)
 ├─ hasOne TradeJournal
 ├─ hasMany TradeChecklistSnapshot
 ├─ hasMany TradeMistake
 ├─ hasOne TradePsychology
 ├─ hasMany TradeRuleViolation
 └─ belongsToMany Tag (via trade_tag)

MistakeCategory   hasMany TradeMistake
PsychologyState   hasMany TradePsychology (via before_psychology_state_id)
TradingRule       hasMany TradeRuleViolation
Tag               belongsToMany Trade

Goal   belongsTo User, TradingAccount · hasMany GoalProgress
MonthlyReview   belongsTo User, TradingAccount, Strategy(best), Strategy(worst)
                · hasMany TradeJournal (back-reference)
```

---

## 11. Enums reference (all Postgres native types)

| Enum type | Values |
|---|---|
| `users_theme_enum` | light, dark, system |
| `trading_accounts_account_type_enum` | personal_live, demo, funded, prop_evaluation, prop_funded, backtesting, custom |
| `trading_accounts_status_enum` | active, archived, closed |
| `trading_accounts_dd_calc_enum` | balance_based, equity_based, trailing |
| `account_transactions_type_enum` | deposit, withdrawal, fee, refund, profit_split, adjustment |
| `account_balance_snapshots_source_enum` | trade_close, scheduled, manual_adjustment |
| `trades_asset_class_enum` | forex, crypto, indices, commodities, stocks, futures, custom |
| `trades_direction_enum` | long, short |
| `trades_outcome_enum` | win, loss, breakeven |
| `trades_htf_bias_enum` | bullish, bearish, neutral, mixed |
| `trades_status_enum` | planned, open, closed, cancelled, invalidated |
| `taxonomy_status_enum` | active, archived (shared by trading_sessions, market_conditions, entry_models, setup_grades, strategy_setups) |
| `mistake_categories_severity_enum` | low, medium, high, critical (shared by trade_mistakes.severity) |
| `trading_rules_severity_enum` | low, medium, high, critical |
| `strategies_status_enum` | active, archived |
| `screenshots_type_enum` | before, during, after, example |
| `goals_goal_type_enum` | account_balance, growth_percent, net_r, max_drawdown, discipline_score, monthly_profit, weekly_profit, min_a_plus_trades, max_rule_violations |
| `goals_status_enum` | active, achieved, failed, archived |
| `analytics_snapshots_period_type_enum` | daily, weekly, monthly |

---

## 12. Design Decisions & Conflict Resolutions (expanded)

1. **`sessions` → `trading_sessions`.** Laravel ships a `sessions` table when the `session`
   driver is `database`; even if Sanctum SPA auth ultimately uses `redis` for sessions, reserving
   the name avoids a landmine if the driver ever changes, and `trading_sessions` is unambiguous
   next to `trade_*` tables in migration listings.

2. **`trading_sessions`, `market_conditions`, `entry_models` schemas.** SRS lists these as
   entities (§50) with example values (§12.2–12.4) but never gives them a fields section the way
   `setup_grades` (§69) gets one. They are designed here as thin, per-user, sortable/archivable
   lookup tables — deliberately the *same shape* as `setup_grades` minus its score-range columns,
   so one generic `TaxonomyController`/`TaxonomyResource` pattern can serve all of them on the
   backend and one generic `<TaxonomyManager>` component can serve all of them on the frontend
   (see `FRONTEND_ARCHITECTURE.md`).

3. **`account_balance_snapshots`, `goal_progress`, `analytics_snapshots` schemas.** Named in the
   §50 entity list, never detailed. Without them, the equity curve (§7.2), drawdown page (§107),
   goal progress charts (§31), and "am I improving month by month" (§118) would each require
   replaying the entire trade/transaction history on every request — unacceptable against the
   §96 performance targets once trade count grows into the thousands (§97). Designed as
   append-only, one-row-per-period tables written by the trade-close pipeline and the daily/
   weekly/monthly scheduler (§86).

4. **Polymorphic `screenshots` instead of `trade_screenshots`.** SRS §14 fully specifies
   trade screenshots; §20.1 separately requires "Example screenshots" on a Strategy. Rather than
   duplicate the table (and duplicate all upload/validation/storage logic in the backend and all
   upload UI in the frontend), one polymorphic table serves both, distinguished by
   `screenshotable_type` and a `type = example` value reserved for the strategy case.

5. **`symbol` + `broker_symbol` replace Symbol/Pair/Market/Broker-symbol.** "Market" is already
   captured by `asset_class`; "Pair" and "Symbol" are the same concept for every asset class this
   system supports (forex pair, crypto pair, index ticker, commodity ticker). Keeping four
   overlapping string fields would create four places for the same fact to disagree; the model
   keeps exactly one canonical identifier plus one explicit override for the rare broker-naming
   mismatch case SRS actually calls out.

6. **`before_emotion_id` → `before_psychology_state_id`.** Purely a naming-consistency fix: the
   table is `psychology_states`, so the FK should read the same way every other FK in this schema
   does (`<referenced_table_singular>_id`).

7. **Delete-safety via soft delete + `status` + `ON DELETE RESTRICT`.** Three layers, not one,
   because SRS is explicit that historical trades must never lose their strategy/setup/etc.
   context (§109) while still allowing users to "Delete unused strategy" (§10):
   - `status = archived` is the *normal* retirement path a user reaches for from the UI — it
     hides the item from new-trade dropdowns without touching any row that references it.
   - `deleted_at` (soft delete) is available for strategies/accounts/trades themselves so an
     accidental delete is recoverable.
   - The FK is still `ON DELETE RESTRICT` at the database level as a last-resort integrity net —
     the application should never actually hit it if the "unused" check before hard-delete is
     implemented correctly, but the constraint means a bug in that check fails loudly (a
     constraint violation) instead of silently orphaning trade history.

8. **Decimal precision exactly per SRS §88**, applied per-column above rather than uniformly, since
   e.g. `trade_mistakes.estimated_cost_r` is an R-multiple (10,4) while
   `trade_mistakes.estimated_cost_amount` is money (20,4).

9. **UUIDs on externally-referenced entities.** Sequential integer IDs are kept as the internal
   PK/FK mechanism (cheaper joins, smaller indexes) but every table whose row is addressed from a
   URL or an uploaded-file path also gets a `uuid`, so route params and signed screenshot URLs
   never leak sequential IDs (SRS §95 — "avoid exposing file paths publicly" generalizes to not
   exposing enumerable identifiers either).

10. **`trades.user_id` is a denormalization, not a second ownership source.** It exists so
    dashboard/analytics queries that filter "all of this user's trades across every account" don't
    need a join through `trading_accounts` on the hottest query paths in the system. The
    `TradeService`/`StoreTradeAction` is the only code path allowed to write `trades.user_id`, and
    it always copies it from the resolved `trading_account.user_id` — this is documented here so
    a future contributor doesn't add a form field that lets it drift from the account's real
    owner.
