# TradeGrowth — Analytics Formulas

**Status:** Design document. This is the exact, unambiguous specification the
`app/Analytics/*Calculator.php` classes (see `ARCHITECTURE.md` §2.1) must implement. Every
formula names its input fields using the exact column names from `DATABASE_SCHEMA.md` so there is
no ambiguity between spec and implementation. All money/R-multiple arithmetic uses `DECIMAL`
math (never float) — in PHP, via `bcmath` or explicit integer-cents arithmetic, never native
float operators on money.

**Trade population note:** unless stated otherwise, every formula below operates over trades
where `status = 'closed'` and `deleted_at IS NULL`, filtered by whatever account/date/taxonomy
filters the request applied (`API_CONTRACTS.md` §8's common query parameters).

---

## 1. Win Rate, Loss Rate, Breakeven Rate

```text
Wins       = COUNT(trades WHERE outcome = 'win')
Losses     = COUNT(trades WHERE outcome = 'loss')
Breakevens = COUNT(trades WHERE outcome = 'breakeven')

Win Rate       = Wins / (Wins + Losses) × 100
Loss Rate      = Losses / (Wins + Losses) × 100
Breakeven Rate = Breakevens / (Wins + Losses + Breakevens) × 100
```

Breakevens are excluded from the Win Rate / Loss Rate denominator (SRS §23.1) — they only appear
in Breakeven Rate, which uses the full population as its denominator. If `Wins + Losses = 0`,
Win Rate and Loss Rate are `null` (undefined), not zero — the frontend renders "—" for a null
metric, never a misleading `0.0%`.

**Example:** 62 wins, 71 losses, 7 breakevens (140 total).
`Win Rate = 62 / (62+71) × 100 = 46.62%`. `Breakeven Rate = 7 / 140 × 100 = 5.00%`.

---

## 2. Gross Profit, Gross Loss, Net Profit

```text
Gross Profit = SUM(net_profit_loss WHERE net_profit_loss > 0)
Gross Loss   = SUM(net_profit_loss WHERE net_profit_loss < 0)     # stays negative
Net Profit   = Gross Profit + Gross Loss                            # = SUM(net_profit_loss) over all closed trades
Net Profit % = Net Profit / Starting Balance (for the filtered period) × 100
```

`trades.net_profit_loss` is already fees/commission/swap-inclusive (per `DATABASE_SCHEMA.md`
§4.1: `gross_profit_loss - fees - commission - swap`), so "Gross Profit"/"Gross Loss" here refer
to the SRS §23.2 sense (sum of winning vs. losing *trade outcomes*), not to `gross_profit_loss`
before-fees. This distinction is deliberate — Profit Factor (below) is meant to measure edge
quality inclusive of trading costs.

---

## 3. Average Winner / Average Loser / Largest Winner / Largest Loser

```text
Average Winner = Gross Profit / Wins
Average Loser  = Gross Loss / Losses            # negative; display as magnitude with an explicit minus sign or a "Loss" label, never bare positive (SRS §23.4)

Largest Winner = MAX(net_profit_loss WHERE outcome = 'win')
Largest Loser  = MIN(net_profit_loss WHERE outcome = 'loss')
```

`null` (not `0`) when `Wins = 0` / `Losses = 0` respectively.

---

## 4. R-Multiple

**Per trade:**
```text
R-Multiple = Net Trade P&L / Initial (Actual) Risk Amount
           = trades.net_profit_loss / trades.actual_risk_amount
```
`actual_risk_amount` is the denominator, never `planned_risk_amount` — the realized R must
reflect what was actually risked, not what was planned (relevant when a trader's actual entry/
stop deviated from plan; the deviation itself is measured separately, see §11).

**When partial exits exist** (`trade_partial_exits` rows present): the trade's overall
`r_multiple` is still `net_profit_loss / actual_risk_amount` using the trade's aggregate
`net_profit_loss` (which already sums all partials + the final exit) — there is no separate
weighting step needed because `net_profit_loss` is already the correct aggregate currency figure.
Each `trade_partial_exits.r_multiple` row is independently `profit_loss / (actual_risk_amount ×
percentage_closed / 100)` — informational, shown in the partial-exit timeline, not summed into
the trade total (summing per-partial R would double-count against the aggregate calculation
above).

`null` if `actual_risk_amount` is `0` or `null` (undefined, not infinite).

**Example:** Risk = $50, Profit = $250 → R = $250 / $50 = **+5.00R**.

**Total R:**
```text
Total R = SUM(r_multiple over the filtered closed trades)
Average R = Total R / COUNT(filtered closed trades with a non-null r_multiple)
```

---

## 5. Profit Factor

```text
Profit Factor = Gross Profit / ABS(Gross Loss)
```
`null` when `Gross Loss = 0` (undefined — a trader with zero losing trades has no finite profit
factor; the UI shows "—" or "∞" with a tooltip explaining why, never a fabricated large number).

**Example:** Gross Profit = $3,200, Gross Loss = −$1,385 → `3200 / 1385 = 2.31`.

---

## 6. Expectancy

**In R:**
```text
Win Rate (decimal)  = Wins / (Wins + Losses)
Loss Rate (decimal) = Losses / (Wins + Losses)
Average Winning R   = SUM(r_multiple WHERE outcome = 'win') / Wins
Average Losing R    = SUM(r_multiple WHERE outcome = 'loss') / Losses     # negative value

Expectancy (R) = (Win Rate × Average Winning R) + (Loss Rate × Average Losing R)
```
Note: SRS §23.6 writes this as a subtraction of the *magnitude* of Average Losing R
(`− (Loss Rate × Average Losing R)` with Average Losing R expressed as a positive magnitude).
This document uses signed values throughout instead (`Average Losing R` is negative, so the
formula is a sum, not a difference) — mathematically identical, chosen because it matches how
`r_multiple` is actually stored (signed) and avoids an extra abs()/negate step in the
calculator. Breakevens are excluded from both the win/loss-rate terms and the averages, matching
§1's Win Rate treatment.

**In currency (informational companion metric, not in SRS but trivially derivable and useful on
the dashboard alongside expectancy-in-R):**
```text
Expectancy (Amount) = (Win Rate × Average Winner) + (Loss Rate × Average Loser)
```

**Example:** Win Rate 44.3%, Avg Winning R +2.10R, Loss Rate 50.7%, Avg Losing R −0.95R.
`Expectancy = (0.443 × 2.10) + (0.507 × −0.95) = 0.9303 − 0.4817 = +0.4487R`.

---

## 7. Risk/Reward Ratio

```text
Risk/Reward Ratio = Average Winner / ABS(Average Loser)
```
Distinct from Profit Factor: this compares the *average size* of a win vs. a loss, independent of
how often each occurs, whereas Profit Factor is a *total-dollars* ratio. Both are useful together
(a trader can have a great R:R but a poor Profit Factor if win rate is too low, and vice versa).

---

## 8. Equity Curve

The equity curve is built from `account_balance_snapshots` (`DATABASE_SCHEMA.md` §3.3), one point
per day in the requested period, not recomputed from raw trades on every request:

```text
For each day D in [period_start, period_end]:
    balance(D) = account_balance_snapshots.balance WHERE snapshot_date = D
    equity(D)  = account_balance_snapshots.equity  WHERE snapshot_date = D
    high_water_mark(D) = MAX(balance(D') for all D' <= D)   # precomputed and stored per row already
```

If multiple accounts are included (no `account_id` filter), the series is the **sum of balances
across all included accounts per day** — accounts are combined by date, not concatenated.

**Deposit/withdrawal handling:** `account_balance_snapshots.balance` already reflects deposits/
withdrawals (they move the literal account balance). The equity curve therefore shows a visible
step on the day of a deposit/withdrawal — this is intentional and correct; it's the **Growth %**
formula (§10) that adjusts for it, not the raw balance series, so the two charts answer different
questions ("what is my balance" vs. "how much have I actually grown it").

---

## 9. Drawdown

**Current Drawdown** (point-in-time, as of the latest snapshot or live balance):
```text
Drawdown(D) = (Equity(D) - High Water Mark up to D) / High Water Mark up to D × 100
```
This is always `≤ 0` (or `0` at a new equity high). Displayed as a positive magnitude in the UI
("Current Drawdown: 1.57%") with the sign implied by the label, matching SRS §30's example.

**Maximum Drawdown:**
```text
Maximum Drawdown = MIN( Drawdown(D) for all D in the equity series )   # most negative value, shown as positive magnitude
```
i.e. the largest peak-to-trough percentage decline observed anywhere in the series, computed by
scanning the ordered `account_balance_snapshots` series once, tracking a running high-water mark
and the minimum `(equity - running_hwm) / running_hwm` seen so far.

**Drawdown Duration:** number of days between the peak date (the high-water-mark date that
preceded the *current* unrecovered drawdown) and the latest date in the series, while
`Drawdown(D) < 0` continuously. Resets to `0` the day equity makes a new high.

**Recovery Factor:**
```text
Recovery Factor = Net Profit / ABS(Maximum Drawdown in currency terms)
```
where "Maximum Drawdown in currency terms" is the largest peak-to-trough *dollar* decline
(computed the same way as the percentage version, but on raw `equity` values instead of the
percentage ratio) over the same period as Net Profit.

**Example (SRS §30):** Balance $5,325, High Water Mark $5,410 →
`Drawdown = (5325 - 5410) / 5410 × 100 = -1.57%` → displayed as **Current Drawdown: 1.57%**.

### 9.1 Funded-account buffers (SRS §30)

```text
Remaining Overall Drawdown Buffer % = max_overall_drawdown_percent - current_drawdown_percent
Remaining Daily Drawdown Buffer %   = max_daily_drawdown_percent - todays_drawdown_percent

Today's Drawdown % = (Equity(now) - Balance at today's daily_reset_time) / Balance at today's daily_reset_time × 100,
                      floored at 0 if positive (a profitable day has no "drawdown used")
```
`daily_reset_time` + `daily_reset_timezone` (`trading_accounts` columns) define "today" — a
funded account's day boundary is the broker's reset clock, not UTC midnight (SRS §89).

**Warning thresholds** (SRS §30, configurable but defaulted): buffer-used percentage of the
relevant limit —
```text
Buffer Used % = current_drawdown_percent / max_overall_drawdown_percent × 100   (or the daily equivalent)
```
UI warns at `50%`, `75%`, `90%`, and shows an exceeded state at `≥ 100%`.

---

## 10. Growth %

```text
Growth % = (Current Adjusted Equity - Initial Balance - Net Deposits) / Initial Balance × 100

where:
  Current Adjusted Equity = trading_accounts.current_equity  (or a historical equity(D) from the snapshot series, for a growth-as-of-date query)
  Net Deposits = SUM(account_transactions.amount WHERE type = 'deposit')
               - SUM(account_transactions.amount WHERE type = 'withdrawal')
               + SUM(account_transactions.amount WHERE type = 'refund')
               - SUM(account_transactions.amount WHERE type = 'fee')
               ± SUM(account_transactions.amount WHERE type IN ('profit_split','adjustment'))   # sign per the individual transaction's recorded effect
```
This isolates *trading-driven* growth from capital movements (SRS §8.3, §23.7) — a $1,000
deposit does not read as +20% "growth" on a $5,000 account. `profit_split`/`adjustment` amounts
are signed at entry time (a profit split is typically a withdrawal-like negative effect; a
correction can go either way), so the formula sums their recorded signed effect directly rather
than assuming a fixed direction.

**Example:** Initial $5,000, no deposits/withdrawals, current equity $5,325 →
`Growth % = (5325 - 5000 - 0) / 5000 × 100 = +6.50%`.

---

## 11. Planned vs. Actual Execution (SRS §18)

```text
Entry Deviation  = actual_entry_price - planned_entry_price
Stop Deviation   = stop_loss_price - planned_stop_loss_price   # if a planned stop is tracked separately; V1 has one stop_loss_price field, so this is 0 unless the stop was moved and logged as a mistake (trade_mistakes), not a separate planned/actual stop pair
Target Deviation = actual_exit_price - planned_take_profit_price

Planned RR = ABS(planned_take_profit_price - planned_entry_price) / ABS(planned_entry_price - stop_loss_price)
Achieved R = r_multiple   (§4 above)

Execution Efficiency = Achieved R / Planned RR × 100    # how much of the planned reward-multiple was actually captured, can exceed 100%

Profit Capture Efficiency = Actual Positive R / Planned Positive R × 100
  where Actual Positive R  = MAX(r_multiple, 0)
        Planned Positive R = Planned RR (only meaningful when the trade was a winner; for a loser this metric is not computed — display "—")
```
Values above 100% are allowed and displayed as-is (SRS §18: "allow values above 100% if exits
outperform the original plan"), not capped.

---

## 12. Setup Quality Score (SRS §21)

A weighted 0–100 score computed **at trade-save time** and stored on `trades.setup_quality_score`
(not recomputed retroactively unless an explicit backfill runs), so historical scores reflect the
checklist/context weights that existed when the trade was actually planned.

```text
Factors (each normalized to a 0–1 sub-score, weight configurable per user via
strategy_checklist_items.weight for checklist items, and fixed default weights for the
non-checklist factors below unless the user overrides them in Settings):

  htf_alignment_score        = 1 if higher_timeframe_bias aligns with the trade direction, else 0
  checklist_completion_score = SUM(weight_snapshot WHERE is_checked) / SUM(weight_snapshot) over trade_checklist_snapshots for this trade
  session_correctness_score  = 1 if trading_session_id is in the strategy's preferred_sessions, else 0
  min_rr_achieved_score      = 1 if planned_rr >= strategy.minimum_rr, else 0
  news_avoidance_score       = 1 unless a trade_mistake with mistake_category matching "Traded News" is attached, else 0
  # additional user-defined confirmation factors: any strategy_checklist_item flagged is_required
  # contributes to checklist_completion_score already; no separate handling needed

Setup Quality Score = Σ(factor_score × factor_weight) / Σ(factor_weight) × 100
```
Default factor weights (editable per user in Settings, stored as a small JSON config rather than
a new table — this is a single user-level preference blob, not a relational entity): HTF
alignment 20%, checklist completion 35%, session correctness 15%, minimum RR achieved 15%, news
avoidance 15%.

This is explicitly a **historical quality score**, never presented as a win-probability
guarantee (SRS §21.1, §21.3, §39) — the frontend copy near this number always reads "Setup
Quality Score" / "Historical Performance", never "Win Probability".

### 12.1 Historical Similarity Summary

For a given set of setup attributes (strategy, session, grade, HTF bias, symbol, etc. — the same
filter set as `GET /analytics/historical-setup`):
```text
Similar Historical Trades = COUNT(closed trades matching all specified filter attributes)
Historical Win Rate       = Win Rate (§1) restricted to that matching set
Average Result            = Average R (§4) restricted to that matching set
Profit Factor              = Profit Factor (§5) restricted to that matching set
```

---

## 13. Streaks

Computed by scanning closed trades in `trade_date` (then `entry_at`) order:

```text
Current Win Streak  = count of consecutive 'win' outcomes ending at the most recent closed trade
                       (0 if the most recent trade is not a win; breakevens do not break or extend a streak, they are skipped)
Current Loss Streak  = same, for 'loss'
Longest Win Streak   = max run length of consecutive 'win' outcomes anywhere in the ordered series (breakevens skipped, not counted as a break)
Longest Loss Streak  = same, for 'loss'
```
Breakeven trades are skipped (neither extend nor reset a streak) rather than counted as a break,
since a breakeven is not a losing outcome and shouldn't be treated as ending a winning run —
this is a deliberate interpretation choice, documented here so the implementation and any future
reviewer agree on it.

---

## 14. Holding Time

```text
Holding Time (minutes) = (exit_at - entry_at) in minutes, per trade
Average Holding Time         = AVG(Holding Time) over closed trades
Average Winning Hold Time    = AVG(Holding Time) WHERE outcome = 'win'
Average Losing Hold Time     = AVG(Holding Time) WHERE outcome = 'loss'
```

---

## 15. Sample Confidence (SRS §24.1)

Applied to any grouped analytics row (per strategy, per pair, per session, etc.) based on that
group's trade count:

```text
1–9 trades    → "Low Sample"
10–29 trades  → "Developing"
30–49 trades  → "Moderate"
50+ trades    → "Reliable"
```
Thresholds are configurable (stored the same way as the Setup Quality Score weights — a small
per-user JSON settings blob, not a table) but these are the shipped defaults. Any UI ranking
strategies/pairs by performance sorts by the metric as usual but visually de-emphasizes
(muted badge, tooltip caveat) rows below "Moderate", per the SRS §24.1 intent of preventing a
3-trade 100%-win-rate strategy from reading as "best."

---

## 16. Rule Compliance (SRS §33)

```text
Compliant Trades   = closed trades with zero rows in trade_rule_violations
Violating Trades    = closed trades with ≥ 1 row in trade_rule_violations

Net R (Compliant)   = SUM(r_multiple) over Compliant Trades
Net R (Violating)   = SUM(r_multiple) over Violating Trades

Rule Compliance %   = COUNT(Compliant Trades) / COUNT(all closed trades) × 100
Estimated R Lost to Violations = SUM(trade_rule_violations.estimated_cost_r) over the filtered period
```

---

## 17. Mistake Cost (SRS §16.3)

```text
Most Common Mistake    = mistake_category with MAX(COUNT(trade_mistakes))
Most Expensive Mistake = mistake_category with MIN(SUM(trade_mistakes.estimated_cost_r))   # most negative
Mistake Loss Rate (per category) = COUNT(trades with that mistake AND outcome = 'loss')
                                    / COUNT(trades with that mistake) × 100
Estimated R Lost to Mistakes (overall) = SUM(trade_mistakes.estimated_cost_r) over the filtered period
```

---

## 18. Worked end-to-end example

A single closed trade, to sanity-check the whole chain:

```text
actual_entry_price = 1.08480, actual_exit_price = 1.08720, stop_loss_price = 1.08350
actual_risk_amount = $50.00, net_profit_loss = $92.50, fees = $0, commission = $0, swap = $0

r_multiple = 92.50 / 50.00 = +1.85R                                        (§4)
outcome = 'win'  (net_profit_loss > 0, and above breakeven threshold config)

If this were the only trade in the filtered period:
  Wins = 1, Losses = 0  → Win Rate = 100%  (§1)
  Gross Profit = $92.50, Gross Loss = $0  → Profit Factor = null (§5, undefined — no losses yet)
  Average Winner = $92.50 (§3)
  Total R = 1.85, Average R = 1.85 (§4)
```
This matches the SRS §18 worked example's Actual R of `+1.85R`.
