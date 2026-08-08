# TradeGrowth — Implementation Plan

**Status:** Design document. Breaks SRS §116's 8 broad phases into small, independently
shippable increments with explicit dependencies, DB tables touched, API endpoints added, and
frontend routes/features added — each pulled directly from `DATABASE_SCHEMA.md`,
`API_CONTRACTS.md`, and `FRONTEND_ARCHITECTURE.md` so this document never restates a design
decision, only sequences it.

**Rule for every phase:** backend (migrations + models + policies + endpoints + tests) and
frontend (types + api hooks + components + pages) for the same feature ship together as one
increment — never a backend-only phase followed by a frontend-only phase, so nothing is built
against a stale contract.

---

## Phase 0 — Architecture & Database Planning *(this phase)*

**Depends on:** nothing.
**Deliverable:** the six docs in `docs/` (`ARCHITECTURE.md`, `DATABASE_SCHEMA.md`,
`API_CONTRACTS.md`, `FRONTEND_ARCHITECTURE.md`, `ANALYTICS_FORMULAS.md`, this file).
**No code changes.**

---

## Phase 1a — Environment, Auth Foundation

**Depends on:** Phase 0.

Backend:
- Switch `DB_CONNECTION` to `pgsql` in `.env`/`.env.example`; provision local Postgres.
- Configure Sanctum SPA mode: `SANCTUM_STATEFUL_DOMAINS`, `SESSION_DOMAIN`, `config/cors.php`
  (`supports_credentials: true`), `EnsureFrontendRequestsAreStateful` in `bootstrap/app.php`
  middleware group (`ARCHITECTURE.md` §3).
- `users` table gains `timezone`, `default_currency`, `theme`, `preferences` (jsonb) columns
  (migration extending the default Laravel `users` table per `DATABASE_SCHEMA.md` §2).
- Auth endpoints: `POST /auth/login`, `POST /auth/logout`, `GET /auth/user`,
  `POST /auth/forgot-password`, `POST /auth/reset-password` (`API_CONTRACTS.md` §2).
- `GET /settings`, `PUT /settings` (`API_CONTRACTS.md` §13) — ships now (not deferred) since
  `next-themes` and the sidebar collapse-state need a durable home from day one; the
  `setup_quality_score_weights`/`sample_confidence_thresholds` fields go live functionally in
  Phases 6/4 respectively but the storage/endpoint exists from the start.
- `UserPolicy` not needed (users don't manage other users in V1); every other Policy created in
  later phases follows the pattern fixed in `ARCHITECTURE.md` §4.

Frontend:
- `src/lib/auth/auth-provider.tsx`, `use-login.ts`, `use-logout.ts`
  (`FRONTEND_ARCHITECTURE.md` §6).
- `(auth)` route group: `login`, `forgot-password`, `reset-password` pages.
- `(dashboard)` route group's `layout.tsx` gains the auth-guard redirect.
- `lib/api/client.ts` gets the `withCredentials`/401-interceptor config.
- `/settings` page: profile fields (timezone, default currency, theme), sidebar-collapse
  preference now backed server-side instead of `localStorage`-only. Analytics-weight/threshold
  editing UI is added later (Phase 4/6) once those features exist to configure.

**Tables touched:** `users` (extended).
**Verification:** register a user (via `tinker`/seeder), log in from the frontend, confirm the
session cookie round-trips and `GET /auth/user` returns the profile; logging out clears it.

---

## Phase 1b — Trading Accounts

**Depends on:** 1a.

Backend: `trading_accounts`, `account_transactions`, `account_balance_snapshots` migrations +
models + `TradingAccountPolicy` + `AccountTransactionPolicy`. Endpoints: full `/accounts` CRUD,
`/accounts/{account}/transactions`, `/accounts/{account}/growth`, `/accounts/{account}/drawdown`
(`API_CONTRACTS.md` §3). `BalanceReconciliationService` (recomputes `current_balance` on every
transaction/trade-close — introduced here, reused from Phase 1d onward).

Frontend: `features/accounts/` (api/components/hooks/types), `/accounts` page, sidebar
`sidebar-account-selector.tsx` wired to real data + Zustand account store
(`FRONTEND_ARCHITECTURE.md` §2.4, §4), empty-account state (SRS §157).

**Tables touched:** `trading_accounts`, `account_transactions`, `account_balance_snapshots`.
**Verification:** create an account, record a deposit, confirm balance updates and the account
selector shows it; growth/drawdown endpoints return zero-trade baseline values without erroring.

---

## Phase 1c — Taxonomy & Seeders

**Depends on:** 1a. (Independent of 1b — can build in parallel.)

Backend: `trading_sessions`, `market_conditions`, `entry_models`, `setup_grades`,
`mistake_categories`, `psychology_states`, `tags`, `trading_rules` migrations + models. One
generic `TaxonomyController`/`TaxonomyResource` pattern (`ARCHITECTURE.md` §2.1,
`API_CONTRACTS.md` §7) parameterized per table. `UserRegisteredSeeder` (or a listener on user
registration) that copies the SRS §112 default values into each per-user taxonomy table.
`strategies`, `strategy_setups`, `strategy_rules`, `strategy_checklist_items` migrations +
models + `StrategyPolicy`; full `/strategies` CRUD incl. setups/checklist/rules sub-resources
(`API_CONTRACTS.md` §6).

Frontend: a generic `<TaxonomyManager>` component (settings-area CRUD list) reused across all
seven simple taxonomy types; `features/strategies/` full build-out;
`/playbook/strategies` list + `/playbook/strategies/[uuid]` detail page with tabs (Overview,
Setups, Checklist, Rules, Screenshots-placeholder, Analytics-placeholder —
`FRONTEND_ARCHITECTURE.md` §1, §3).

**Tables touched:** `trading_sessions`, `market_conditions`, `entry_models`, `setup_grades`,
`mistake_categories`, `psychology_states`, `tags`, `trading_rules`, `strategies`,
`strategy_setups`, `strategy_rules`, `strategy_checklist_items`.
**Verification:** new user registration produces seeded defaults in every taxonomy table;
create/archive/delete a custom strategy and a custom mistake category from the UI.

---

## Phase 1d — Core Trade CRUD

**Depends on:** 1b, 1c.

Backend: `trades` migration + model + `TradePolicy`. `StoreTradeAction`, `UpdateTradeAction`,
`CloseTradeAction`, `RestoreTradeAction` (`ARCHITECTURE.md` §2.1) implementing the validation
rules from `API_CONTRACTS.md` §14 and the server-authoritative recalculation rule (net P&L,
R-multiple — using `ANALYTICS_FORMULAS.md` §2–4 — nothing else from the formulas doc yet).
Endpoints: `/trades` CRUD, `/trades/{trade}/close`, `/trades/{trade}/restore`
(`API_CONTRACTS.md` §4, screenshots/journal/mistakes/psychology/checklist/tags sub-resources
deferred to 1e/1f). `trade_partial_exits` migration + model + endpoint.

Frontend: `features/trades/` full build-out — `types/schema.ts` (zod, sections per
`FRONTEND_ARCHITECTURE.md` §5.1 minus Psychology/Mistakes/Journal/Screenshots sections, added in
1e/1f), trade form (Account & Market, Trade Plan, Strategy & Setup, Risk, Execution, Result
sections only), `trade-table.tsx`/`trade-card.tsx`, `/trades`, `/trades/new`, `/trades/[uuid]`
(detail page skeleton — journal/mistake/psychology/screenshot panels added later phases).
Dashboard's `recent_trades` slice can go live once this phase ships trades to read.

**Tables touched:** `trades`, `trade_partial_exits`.
**Verification:** create a planned trade, close it with an exit price, confirm
`net_profit_loss`/`r_multiple`/`outcome` are computed server-side and match a hand-calculated
expectation from `ANALYTICS_FORMULAS.md` §4; confirm it appears in `/trades` and updates the
account balance from Phase 1b.

---

## Phase 1e — Journal, Checklist, Mistakes, Psychology, Rule Violations

**Depends on:** 1d.

Backend: `trade_journals`, `trade_checklist_snapshots`, `trade_mistakes`, `trade_psychology`,
`trade_rule_violations`, `tags`/`trade_tag` wiring into `StoreTradeAction`/`UpdateTradeAction`
(checklist snapshot copy-on-save logic per `DATABASE_SCHEMA.md` §7.5 — this is the one place
"snapshot, don't reference live data" matters, implement carefully and test that editing a
strategy's checklist afterward does not change a past trade's snapshot).

Frontend: remaining trade form sections (Psychology, Mistakes, Journal), trade detail page's
full layout (SRS §36: Planned vs Actual, Checklist, Before/During/After Journal, Mistakes,
Psychology, Rule Violations, Lessons), journal completeness indicator
(`FRONTEND_ARCHITECTURE.md` §5.3).

**Tables touched:** `trade_journals`, `trade_checklist_snapshots`, `trade_mistakes`,
`trade_psychology`, `trade_rule_violations`, `tags`, `trade_tag`.
**Verification:** save a trade with a full journal + 2 mistakes + psychology + a checklist
snapshot; edit the source strategy's checklist afterward and confirm the trade's snapshot is
unchanged; confirm journal completeness % updates as fields are filled in.

---

## Phase 1f — Screenshots

**Depends on:** 1d. (Independent of 1e — can build in parallel.)

Backend: polymorphic `screenshots` migration + model (`DATABASE_SCHEMA.md` §4.3), local-disk
`Storage` config, `ProcessScreenshotUpload` job (thumbnail + validation,
`ARCHITECTURE.md` §6), signed-view endpoint (`ARCHITECTURE.md` §8), `/trades/{trade}/screenshots`
CRUD (`API_CONTRACTS.md` §5).

Frontend: upload UI (drag/drop, progress state per `FRONTEND_ARCHITECTURE.md` §7),
`screenshot-timeline.tsx` shared component, wired into trade detail page and (in Phase 1c's
already-built) strategy detail page's Screenshots tab (`type = example`).

**Tables touched:** `screenshots`.
**Verification:** upload a before/after screenshot to a trade, confirm it displays via the
signed-view endpoint (not a raw public path); confirm a strategy's example screenshot uses the
same underlying table with `type = example`.

---

## Phase 2 — Performance Engine & Dashboard

**Depends on:** 1d, 1e (needs real trade data with journal/mistake context to be meaningful; 1f
optional — screenshots aren't required for analytics correctness).

Backend: `PerformanceMetricsCalculator`, `EquityCurveCalculator`, `DrawdownCalculator`
(`ARCHITECTURE.md` §2.1) implementing `ANALYTICS_FORMULAS.md` §1–10, §13–14. `SnapshotAccountBalance`
job wired to trade-close (already referenced by 1d's `CloseTradeAction`) and the nightly
scheduler backfill. `/analytics/overview`, `/analytics/equity-curve`, `/analytics/drawdown`,
`/dashboard` endpoints (`API_CONTRACTS.md` §8, §11).

Frontend: `/dashboard` page fully built (KPI cards, equity curve chart, performance summary,
milestones — SRS §7), `equity-curve-chart.tsx`, `drawdown-progress.tsx`, `metric-card.tsx` shared
components; `/performance/growth`, `/performance/equity`, `/performance/drawdown` pages.

**Tables touched:** reads `trades`, `account_balance_snapshots`; writes
`account_balance_snapshots` (via the close-trade hook, already modeled in 1d/1b).
**Verification:** with ≥ 10 closed trades across a few days, confirm dashboard KPIs match
hand-calculated values from `ANALYTICS_FORMULAS.md`, and the equity curve/drawdown charts render
without gaps.

---

## Phase 3 — Trading Calendar

**Depends on:** Phase 2 (reuses its P&L/R aggregation logic, just grouped by day instead of
period).

Backend: `/calendar/month`, `/calendar/day/{date}` endpoints (`API_CONTRACTS.md` §9).

Frontend: `monthly-calendar.tsx` shared component, `/calendar` page (month/week/day views per
SRS §19.1), day-detail drill-down (dialog or route), dashboard's `calendar_preview` slice goes
live.

**Tables touched:** reads `trades` only (no new tables).
**Verification:** a day with 2 trades shows correct win/loss/net-P&L aggregation on the calendar
cell and matches the day-detail drill-down.

---

## Phase 4 — Analytics Pages (per-dimension)

**Depends on:** Phase 2 (shares `PerformanceMetricsCalculator` primitives), 1c (needs taxonomy
tables to group by).

Backend: `StrategyAnalytics`, `PairAnalytics`, `SessionAnalytics`, `WeekdayAnalytics`,
`MistakeAnalytics`, `PsychologyAnalytics`, `RiskAnalytics`, `RuleComplianceAnalytics`
(`ARCHITECTURE.md` §2.1, formulas from `ANALYTICS_FORMULAS.md` §15–17). Endpoints:
`/analytics/strategies`, `/pairs`, `/sessions`, `/weekdays`, `/setup-grades`, `/mistakes`,
`/psychology`, `/risk`, `/rule-compliance` (`API_CONTRACTS.md` §8).

Frontend: `/analytics/*` pages (Overview already live from Phase 2; add Strategies, Pairs,
Sessions, Weekdays, Setup Grades, Mistakes, Psychology, Risk), `strategy-performance-table.tsx`,
`mistake-breakdown-chart.tsx`, `psychology-performance-chart.tsx`, shared `filter-bar.tsx` wired
to the common analytics filter set (`FRONTEND_ARCHITECTURE.md` §3). `/settings` page gains the
`sample_confidence_thresholds` editing UI (`ANALYTICS_FORMULAS.md` §15).

**Tables touched:** reads only — `trades` joined against every taxonomy/journal/mistake/
psychology/rule-violation table.
**Verification:** each analytics page's totals reconcile against `/analytics/overview`'s totals
when summed across all groups (e.g. sum of per-strategy `net_r` equals overview's `total_r`).

---

## Phase 5 — Playbook Completion

**Depends on:** 1c (strategy detail page skeleton), Phase 4 (strategy analytics).

Backend: `/strategies/{strategy}/performance` endpoint (`API_CONTRACTS.md` §6).

Frontend: strategy detail page's Analytics tab goes live (SRS §20.4 historical performance
block), `/playbook/setups`, `/playbook/rules`, `/playbook/checklists` cross-strategy management
views (list-all-across-strategies, distinct from the per-strategy tabs already built in 1c).

**Tables touched:** reads only.
**Verification:** a strategy's Analytics tab numbers match its row in `/analytics/strategies`.

---

## Phase 6 — Historical Setup Analysis

**Depends on:** Phase 4 (reuses filtering infrastructure), 1e (needs checklist snapshots +
HTF bias data to score against).

Backend: `HistoricalSetupAnalyzer`, `SetupQualityScoreCalculator`
(`ANALYTICS_FORMULAS.md` §12). `setup_quality_score` computation wired into
`StoreTradeAction`/`UpdateTradeAction` (retroactively backfilled via a one-off command for
trades created before this phase shipped). `/analytics/historical-setup`,
`/analytics/historical-setup/score`, `/analytics/compare` endpoints (`API_CONTRACTS.md` §8).

Frontend: Setup Quality Score preview panel in the trade form (live-updates as strategy/setup/
session/grade/checklist fields change, per `ANALYTICS_FORMULAS.md` §12.1's Historical Similarity
Summary), `analytics-comparison.tsx` shared component + a comparison page/panel (SRS §40 example
pairs: A+ vs B, London vs NY, etc.). `/settings` page gains the `setup_quality_score_weights`
editing UI (`ANALYTICS_FORMULAS.md` §12).

**Tables touched:** `trades.setup_quality_score` (backfill), reads everything else.
**Verification:** the trade-form's live Setup Quality Score preview matches a manually-run
`/analytics/historical-setup/score` call with the same inputs.

---

## Phase 7 — Reports, Export, Goals, Reviews

**Depends on:** Phase 4 (reports are mostly re-presentations of analytics), 1b (goals scope to
accounts).

Backend: `goals`, `goal_progress`, `monthly_reviews`, `analytics_snapshots` migrations + models.
`ExportTradesJob`, `GenerateMonthlyReview`, `CheckGoalProgress`, `GenerateAnalyticsSnapshot`
jobs + scheduler entries (`ARCHITECTURE.md` §6). `/reports/*`, `/reports/export`,
`/reports/export/{export}`, `/goals` CRUD, `/goals/{goal}/progress` endpoints
(`API_CONTRACTS.md` §10, §12). CSV/Excel export via a queued job (not synchronous, per SRS §85).

Frontend: `/reports` page (filter-driven report builder, SRS §37.1), `/performance/goals` page,
`goal-progress-card.tsx`, milestone progress UI on the dashboard (SRS §7.4), weekly/monthly
review pages/panels (SRS §38, §108).

**Tables touched:** `goals`, `goal_progress`, `monthly_reviews`, `analytics_snapshots`.
**Verification:** create a growth-percent goal, confirm `goal_progress` accumulates via the
nightly job (or a manual trigger in dev); trigger an export and confirm the queued job produces
a downloadable file; confirm a monthly review auto-generates for a past month with trade data.

---

## Phase 8 — Search, Global Polish, Audit Log

**Depends on:** all prior phases (search indexes content from every domain; polish touches every
page already built).

Backend: `audit_logs` migration + model, write-hooks on trade/account/strategy mutations
(`DATABASE_SCHEMA.md` §9.1). `/search` endpoint with Postgres full-text search
(`API_CONTRACTS.md` §13). Rate limiting tuning, security review pass against
`ARCHITECTURE.md` §10's checklist.

Frontend: global command-palette search (shadcn `Command`), accessibility pass against
`FRONTEND_ARCHITECTURE.md`/SRS §92 checklist, responsive pass against SRS §91, dark/light theme
QA across every page, test suite install (Vitest + RTL + Playwright, per
`FRONTEND_ARCHITECTURE.md` §9) covering the SRS §110.2 critical E2E flow list.

**Tables touched:** `audit_logs`.
**Verification:** SRS §117's MVP Acceptance Criteria (27 items) and §118's Success Criteria
checklist both pass end-to-end; the SRS §110.2 Playwright flow (login → account → strategy →
trade → screenshot → journal → close → calendar → strategy analytics → dashboard update) runs
green.

---

## Dependency graph (summary)

```text
Phase 0
  └─ Phase 1a
       ├─ Phase 1b ──┐
       └─ Phase 1c ──┼─ Phase 1d ──┬─ Phase 1e ──┐
                       │            └─ Phase 1f    │
                       │                            ├─ Phase 2 ──┬─ Phase 3
                       │                            │             ├─ Phase 4 ──┬─ Phase 5
                       │                            │             │             └─ Phase 6
                       │                            │             └─ Phase 7
                       └────────────────────────────┴─────────────┴─────────── Phase 8
```

Phases 1b and 1c can be built in parallel (both only depend on 1a). Phases 1e and 1f can be
built in parallel (both only depend on 1d). Within Phase 4, the eight per-dimension analytics
endpoints/pages are themselves independent of each other and can be parallelized across
sessions/contributors.
