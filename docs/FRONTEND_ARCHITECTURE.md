# TradeGrowth — Frontend Architecture

**Status:** Design document. Reconciles SRS §44 and §122–163 (sidebar spec) with what's already
scaffolded in `frontend/` (see repo state below) into a concrete build target for Phase 1+.

**Current scaffolding (as of Phase 0):** Next.js 16 (App Router) + React 19 + TypeScript,
Tailwind v4, shadcn/ui (`components.json`: style `base-nova`, baseColor `neutral`) with ~18
components already generated, `@tanstack/react-query` + `react-hook-form` + `@hookform/resolvers`
+ `zod` + `zustand` + `axios` + `recharts` + `date-fns` + `next-themes` + `sonner` all installed.
`src/lib/api/client.ts` and `query-client.tsx` exist and are wired into `app/layout.tsx` alongside
`TooltipProvider`/`Toaster`. `src/features/{domain}/{api,components,hooks,types}` placeholder
folders already exist for every SRS module. `app/page.tsx` is still the default landing page —
not yet replaced. No test runner installed yet (added in the phase that needs it, per
`IMPLEMENTATION_PLAN.md`).

---

## 1. Route map (App Router)

Route groups: `(auth)` for unauthenticated pages, `(dashboard)` for the authenticated shell that
renders the sidebar (SRS §144).

```text
src/app/
├── layout.tsx                        # root: QueryProvider, ThemeProvider, TooltipProvider, Toaster
├── (auth)/
│   ├── layout.tsx                    # centered, no sidebar
│   ├── login/page.tsx
│   ├── forgot-password/page.tsx
│   └── reset-password/page.tsx
└── (dashboard)/
    ├── layout.tsx                    # AppShell: sidebar + mobile header + main content slot
    ├── dashboard/page.tsx
    ├── trades/
    │   ├── new/page.tsx
    │   ├── page.tsx                  # journal list (table/card view)
    │   └── [uuid]/page.tsx           # trade detail (SRS §36)
    ├── calendar/page.tsx
    ├── screenshots/page.tsx          # cross-trade screenshot browser
    ├── analytics/
    │   ├── page.tsx                  # Overview
    │   ├── strategies/page.tsx
    │   ├── pairs/page.tsx
    │   ├── sessions/page.tsx
    │   ├── weekdays/page.tsx
    │   ├── setup-grades/page.tsx
    │   ├── mistakes/page.tsx
    │   ├── psychology/page.tsx
    │   └── risk/page.tsx
    ├── performance/
    │   ├── growth/page.tsx
    │   ├── equity/page.tsx
    │   ├── drawdown/page.tsx
    │   ├── daily/page.tsx
    │   ├── monthly/page.tsx
    │   └── goals/page.tsx
    ├── playbook/
    │   ├── strategies/page.tsx
    │   ├── strategies/[uuid]/page.tsx   # tabs: Overview/Trades/Setups/Checklist/Rules/Screenshots/Analytics (SRS §160)
    │   ├── setups/page.tsx
    │   ├── rules/page.tsx
    │   └── checklists/page.tsx
    ├── reports/page.tsx
    ├── accounts/page.tsx
    └── settings/page.tsx
```

This is a 1:1 mapping of SRS §148's route list onto Next's App Router, using route groups
instead of separate top-level apps so the two very different chromes (auth vs. dashboard) don't
leak layout into each other.

**Nesting rule (SRS §159–160):** the sidebar stops at two levels (`Analytics → Strategies`, not
deeper). Anything past that — a strategy's own sub-views — becomes **page-level tabs** inside
`playbook/strategies/[uuid]/page.tsx`, not additional sidebar entries or routes.

---

## 2. Application shell & sidebar

### 2.1 Layout components (SRS §145)

```text
src/components/layout/
├── app-shell.tsx              # grid: sidebar column + main column, handles collapse width
├── app-sidebar.tsx            # composes header/account-selector/add-trade/nav/footer
├── sidebar-header.tsx         # logo, product name, collapse toggle
├── sidebar-account-selector.tsx  # Popover/DropdownMenu, SRS §128
├── sidebar-nav.tsx            # renders groups from src/config/navigation.ts inside ScrollArea
├── sidebar-nav-group.tsx      # uppercase muted group label + item list
├── sidebar-nav-item.tsx       # icon + label + active state + tooltip (collapsed)
├── sidebar-footer.tsx         # avatar, name, DropdownMenu (Profile/Theme/Settings/Logout)
├── mobile-header.tsx          # sticky top bar, menu button, SRS §140
├── mobile-sidebar.tsx         # shadcn Sheet, side="left", SRS §139
└── page-header.tsx            # per-page title/breadcrumb/actions slot
```

`app-shell.tsx` renders `app-sidebar.tsx` (desktop, `hidden md:flex`) and `mobile-header.tsx` +
`mobile-sidebar.tsx` (mobile, `md:hidden`) side by side — no duplicated logo/header markup
between the two per SRS §140's explicit warning against that.

### 2.2 Navigation configuration (SRS §146–147)

```typescript
// src/config/navigation.ts
export type SidebarNavItem = {
  title: string
  href: string
  icon: LucideIcon
  badge?: number | string
  match?: string[]        // extra path prefixes that should also mark this item active
}
export type SidebarNavGroup = {
  label?: string           // omitted for top-level items like Dashboard/Reports/Accounts/Settings
  items: SidebarNavItem[]
}
export const navigation: SidebarNavGroup[] = [ /* Dashboard, Trading, Analytics,
  Performance, Playbook, Reports, Accounts, Settings — exact grouping per SRS §125 */ ]
```

Icon mapping follows SRS §132 exactly (Lucide only, no mixed icon libraries).

### 2.3 Collapse & responsive behavior

- Desktop expanded: `272px`; collapsed: `72px` (SRS §126 recommended values). Persisted to
  `localStorage` (`sidebar:collapsed`), read via a small `useSidebarState()` hook so the choice
  survives reloads without a flash — hydrate from `localStorage` in a `useEffect`, default
  expanded on first paint to avoid SSR/client mismatch.
- Breakpoints (SRS §141): `< 768px` mobile (drawer), `768–1199px` tablet (collapsed 72px
  default), `>= 1200px` desktop (expanded default).
- Collapse transition is a CSS `width`/`margin-left` transition on `app-shell.tsx`'s grid
  columns — no JS-driven layout thrash, main content uses `margin-left` matching sidebar width
  exactly (SRS §144) so nothing is done per-page.
- `sidebar-account-selector.tsx` and `sidebar-nav-item.tsx` both render a `Tooltip` (shadcn) in
  collapsed mode; expanded mode renders plain text — one component with a `collapsed` prop, not
  two components, to avoid drift.

### 2.4 Account selector, Add Trade, drawdown widget

- Account selector (SRS §128) is a client component reading the currently-selected account from
  a small Zustand store (`src/lib/store/account-store.ts`, `persist` middleware →
  `localStorage`), **not** URL state — selecting an account should feel instant and affect every
  page's data fetching (via the query key, §4 below) without a navigation.
- Add Trade button (SRS §129) is a shadcn `Button` using the primary token, always visible above
  the nav list; keyboard shortcut `N` (ignored while a form field has focus — checked via
  `document.activeElement` tag name) navigates to `/trades/new`.
- Funded-account drawdown warning (SRS §151) renders only when the selected account's
  `account_type` is `funded`/`prop_evaluation`/`prop_funded`, using shadcn `Progress`, sourced
  from `GET /accounts/{account}/drawdown` (already fetched for the account selector's balance
  display via the same query, no extra request).
- Empty-account state (SRS §157): account selector renders "No Trading Account / + Create
  Account"; Add Trade is disabled with a tooltip explaining why, rather than silently redirecting
  — clearer for a first-time user.

---

## 3. Feature-folder contract

Every `src/features/{domain}/` folder (already scaffolded empty) follows the same internal
shape, so any contributor can predict where something lives without asking:

```text
src/features/trades/
├── api/
│   ├── queries.ts        # useTradesQuery, useTradeQuery(uuid) — TanStack Query hooks
│   ├── mutations.ts      # useCreateTrade, useUpdateTrade, useCloseTrade, useDeleteTrade
│   └── keys.ts            # query key factory, see §4
├── components/
│   ├── trade-table.tsx
│   ├── trade-card.tsx
│   ├── trade-form/        # multi-section form, §5 below
│   └── ...
├── hooks/
│   └── use-trade-filters.ts   # URL-synced filter state for this feature's list page
└── types/
    ├── schema.ts           # zod schemas (source of truth)
    └── index.ts            # types inferred via z.infer<typeof schema>, re-exported
```

`api/` is the **only** place `lib/api/client.ts` is imported from within a feature — components
never call `axios`/`fetch` directly, they call a query/mutation hook. `types/schema.ts` is the
single source of truth for a domain's shape: the same zod schema drives both the React Hook Form
resolver and (via `z.infer`) the TypeScript type used by the query hooks — the API response type
and the form's validated type are never declared twice.

Shared, cross-feature building blocks (SRS §101) live outside `features/`, in
`src/components/`:

```text
src/components/
├── ui/                     # shadcn primitives (already present)
├── layout/                 # §2.1 above
└── shared/
    ├── account-selector-trigger.tsx
    ├── metric-card.tsx
    ├── growth-badge.tsx
    ├── pnl-value.tsx
    ├── r-multiple-badge.tsx
    ├── outcome-badge.tsx
    ├── setup-grade-badge.tsx
    ├── strategy-badge.tsx
    ├── drawdown-progress.tsx
    ├── equity-curve-chart.tsx
    ├── win-loss-chart.tsx
    ├── monthly-calendar.tsx
    ├── screenshot-timeline.tsx
    ├── filter-bar.tsx
    ├── date-range-filter.tsx
    └── analytics-comparison.tsx
```

`pnl-value.tsx`, `growth-badge.tsx`, `outcome-badge.tsx` all read the positive/negative/neutral
semantic tokens (SRS §45.4) — never a hardcoded `text-green-500`/`text-red-500` — and never rely
on color alone (SRS §92): each renders a `+`/`−` sign or an icon alongside the color.

---

## 4. TanStack Query conventions

Query key factory per feature, hierarchical so a broad invalidation (`['trades']`) and a narrow
one (`['trades', 'detail', uuid]`) both work:

```typescript
// src/features/trades/api/keys.ts
export const tradeKeys = {
  all: ['trades'] as const,
  lists: () => [...tradeKeys.all, 'list'] as const,
  list: (filters: TradeFilters) => [...tradeKeys.lists(), filters] as const,
  details: () => [...tradeKeys.all, 'detail'] as const,
  detail: (uuid: string) => [...tradeKeys.details(), uuid] as const,
}
```

Every list query key includes the **currently selected account** (from the Zustand store, §2.4)
as part of its `filters` object — switching accounts is then just a normal query-key change,
TanStack Query handles refetch/cache automatically, no manual invalidation needed for that case.

**Mutation → invalidation map** (mirrors the backend's cache-invalidation events in
`ARCHITECTURE.md` §7, so frontend and backend agree on what "stale" means):

| Mutation | Invalidates |
|---|---|
| create/update/close/delete Trade | `tradeKeys.all`, `['dashboard']`, `['analytics']`, `['calendar']`, `accountKeys.detail(accountId)` (balance changed) |
| create Account Transaction | `accountKeys.all`, `['dashboard']` |
| create/update/archive Strategy or any taxonomy item | that feature's own list key only (trades already fetched keep their denormalized strategy name until next natural refetch) |
| create/update Goal | `goalKeys.all`, `['dashboard']` |

Global defaults (`query-client.tsx`, already present): `staleTime` short (≈30s) for
frequently-changing data (trades, dashboard), longer (≈5min) for taxonomy lists that rarely
change — set per-hook via `staleTime` override, not globally uniform.

---

## 5. Forms: React Hook Form + Zod

Every form component: `useForm({ resolver: zodResolver(schema), defaultValues })` from the
feature's `types/schema.ts`. Inline field errors render from `formState.errors` using shadcn's
`Form`/`FormField`/`FormMessage` components (already installed).

### 5.1 Trade form (SRS §102)

Multi-section, single page, collapsible sections once the form is long:

```text
1. Account & Market   2. Trade Plan   3. Strategy & Setup   4. Risk
5. Execution          6. Result       7. Psychology          8. Mistakes
9. Journal            10. Screenshots
```

Implementation: one `zod` schema per section (`accountMarketSchema`, `tradePlanSchema`, …)
merged via `z.object({...}).merge(...)` into the form's overall schema, so each section can be
unit-tested independently and the form component can render sections as accordion items
(shadcn `Accordion`) without prop-drilling validation logic.

Auto-calculated fields (planned RR, risk amount, net P&L, P&L %, R-multiple) are derived with
`useWatch` + `useMemo` inside the relevant section, displayed as read-only computed fields with
an explicit "Override" toggle per SRS §102's "manual override should be possible" — toggling
override turns the field editable and marks it dirty so the submit payload includes the
trader's value; the backend still recomputes authoritatively per `API_CONTRACTS.md` §1 and the
response (not the optimistic client value) is what's rendered after save.

### 5.2 Quick Add Trade (SRS §103)

A reduced schema (`quickTradeSchema`, a strict subset of the full trade schema) reused by a
compact dialog/sheet variant of the same form — same zod source, different UI shell, so the two
entry points can never validate differently.

### 5.3 Journal completeness (SRS §104)

Computed client-side from the currently-loaded trade detail (strategy set? setup set? result
entered? before/after screenshot present? lesson filled? mistake reviewed? psychology reviewed?)
via a small pure function in `features/trades/hooks/use-journal-completeness.ts` — no dedicated
API field (matches the "computed, not stored" note in `DATABASE_SCHEMA.md` §4.4), but the trade
list/detail already surfaces a server-computed `journal_completeness_percent` per
`API_CONTRACTS.md` §4 that this hook can fall back to/reconcile against for consistency between
list view (server value) and the in-progress edit view (live client value while editing).

---

## 6. Auth

- `POST /sanctum/csrf-cookie` is called once on app bootstrap (in the root layout's client auth
  provider) before the first mutating request, per `ARCHITECTURE.md` §3's flow.
- `src/lib/auth/` (currently empty) gets: `auth-provider.tsx` (wraps the app, exposes
  `useAuth()` via context, calls `GET /auth/user` on mount through a TanStack Query hook with
  `retry: false` so an unauthenticated visit fails fast), `use-login.ts`/`use-logout.ts`
  mutations.
- Route protection: `(dashboard)/layout.tsx` is a client component that reads `useAuth()` and
  redirects to `/login` when unauthenticated (no `middleware.ts` cookie-sniffing, since the
  session cookie is `httpOnly` and unreadable from Next.js middleware without a round trip
  anyway — the client-side check after the `auth/user` fetch is the simplest correct approach
  for a cookie-session SPA).
- `lib/api/client.ts` (already present) is configured with `withCredentials: true` (axios) and a
  response interceptor that redirects to `/login` on a `401`, matching `API_CONTRACTS.md`'s
  auth error contract.

---

## 7. Error & loading states

- API errors: axios interceptor surfaces `error.response.data.message` via `sonner` toast for
  anything not handled inline; `422` validation errors are mapped onto the RHF form's field
  errors instead of a toast (SRS §99).
- Loading: skeleton components per SRS §100 (`Skeleton` from shadcn) for cards/charts/table rows;
  no full-screen loaders for in-app navigation, only for the initial auth bootstrap.
- Empty states (SRS §98): each feature's list/dashboard component renders a dedicated empty-state
  block (icon + message + primary action) when its query resolves to zero rows — copy pulled
  verbatim from the SRS examples where given.

---

## 8. Theming

`next-themes` (already installed) drives `dark`/`light`/`system`, synced to `users.theme` on
login (server value wins on first load; subsequent client toggles call
`PUT /api/v1/settings` — `API_CONTRACTS.md` §13 — which also persists `sidebar_collapsed` and
the analytics preference blobs, superseding the plain-`localStorage`-only persistence noted in
§2.3 above once Phase 1a's settings endpoint ships). All positive/negative/neutral/sidebar tokens are CSS
variables (SRS §45.4, §142) defined once in `globals.css`, never hardcoded hex in components.

---

## 9. Testing (introduced when Phase 1 needs it, not before)

Per SRS §110.2: Vitest + React Testing Library for unit/component tests, Playwright for the
critical E2E flows listed in SRS §110.2 (login → create account → create strategy → create trade
→ upload screenshot → journal → close → calendar → strategy analytics → dashboard update). Not
installed in Phase 0; `IMPLEMENTATION_PLAN.md` schedules the install alongside the first feature
phase that has enough surface area to test meaningfully (Phase 1d, core trade CRUD).
