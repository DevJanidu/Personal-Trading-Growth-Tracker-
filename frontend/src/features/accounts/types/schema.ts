import { z } from "zod"

// Mirrors trading_accounts_account_type_enum (DATABASE_SCHEMA.md §3.1).
export const accountTypes = [
  "personal_live",
  "demo",
  "funded",
  "prop_evaluation",
  "prop_funded",
  "backtesting",
  "custom",
] as const

export const accountTypeLabels: Record<(typeof accountTypes)[number], string> = {
  personal_live: "Personal Live",
  demo: "Demo",
  funded: "Funded",
  prop_evaluation: "Prop Evaluation",
  prop_funded: "Prop Funded",
  backtesting: "Backtesting",
  custom: "Custom",
}

// Mirrors App\Http\Requests\TradingAccounts\StoreTradingAccountRequest.
export const createTradingAccountSchema = z.object({
  name: z.string().trim().min(1, "Name is required").max(255),
  account_type: z.enum(accountTypes),
  broker: z.string().trim().max(255).optional().or(z.literal("")),
  currency: z
    .string()
    .trim()
    .length(3, "Use a 3-letter currency code, e.g. USD")
    .toUpperCase(),
  initial_balance: z.coerce.number().min(0, "Must be zero or greater"),
  account_created_date: z.string().min(1, "Date is required"),
})

// `initial_balance` uses z.coerce, so the form's pre-validation input type
// (string from an <input>) differs from the validated/submitted output type
// (number) — see create-account-dialog.tsx's 3-generic useForm<> for how
// these two are threaded through react-hook-form.
export type CreateTradingAccountFormInput = z.input<
  typeof createTradingAccountSchema
>
export type CreateTradingAccountInput = z.output<
  typeof createTradingAccountSchema
>

// Mirrors App\Http\Resources\TradingAccountResource.
export type TradingAccount = {
  id: string
  name: string
  account_type: (typeof accountTypes)[number]
  broker: string | null
  currency: string
  initial_balance: string
  current_balance: string
  current_equity: string
  status: "active" | "archived" | "closed"
  max_overall_drawdown_percent: string | null
  max_daily_drawdown_percent: string | null
  profit_target_percent: string | null
  minimum_trading_days: number | null
  maximum_trading_days: number | null
  payout_target: string | null
  consistency_rule_percent: string | null
  drawdown_calculation_type: string | null
  daily_reset_time: string | null
  daily_reset_timezone: string | null
  challenge_phase: string | null
  account_created_date: string
  notes: string | null
  created_at: string
  updated_at: string
}
