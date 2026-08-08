# Trading Growth Tracking System
## Software Requirements Specification (SRS)

**Document Version:** 1.0  
**Project Type:** Personal / Multi-Account Trading Journal and Performance Analytics Platform  
**Frontend:** Next.js, TypeScript, shadcn/ui, Tailwind CSS  
**Backend:** Laravel 13 REST API  
**Database:** MySQL 8+ or PostgreSQL 16+  
**Primary Goal:** Provide a professional trading process tracker that records every trade, measures execution quality, identifies the highest-performing strategies and conditions, tracks account growth, and converts trading history into actionable performance insights.

---

# 1. Introduction

## 1.1 Purpose

The Trading Growth Tracking System is designed to act as a complete trading performance operating system rather than a basic trade journal.

The system must allow a trader to:

- Record every completed or planned trade.
- Attach screenshots before, during, and after the trade.
- Record trade pair, direction, entries, stops, targets, exit values, risk, profit, loss, and R-multiple.
- Assign trades to strategies and setup categories.
- Track strategy performance over time.
- Identify the most profitable and least profitable strategies.
- Calculate win rate, profit factor, expectancy, average R, drawdown, growth, and other professional trading metrics.
- Record mistakes, lessons, emotions, discipline, and execution quality.
- Compare planned trades against actual execution.
- Display results on a visual trading calendar.
- Track account growth in percentage and monetary terms.
- Track multiple trading accounts.
- Support funded/prop-account drawdown rules.
- Create reports based on pairs, strategies, sessions, weekdays, trade grades, mistakes, emotions, and market conditions.
- Build a personal strategy playbook from real historical data.
- Calculate a historical setup quality score without claiming guaranteed future win probabilities.

The application should help answer:

> Which trading conditions actually produce the best results for this trader?

---

# 2. Product Vision

The application should treat trading as a measurable process:

```text
Plan
  ↓
Execute
  ↓
Record
  ↓
Review
  ↓
Measure
  ↓
Identify Patterns
  ↓
Improve
```

The product must focus on process quality and repeatability rather than only profit or win rate.

The system should help the trader determine:

- Which strategy performs best.
- Which pairs perform best.
- Which trading sessions perform best.
- Which weekdays perform best.
- Which trade grades perform best.
- Which market conditions perform best.
- Which mistakes cause the largest losses.
- Which emotional states correlate with poor execution.
- Whether the trader follows their own trading plan.
- Whether profits are coming from a repeatable edge or random outcomes.
- Whether the trader is improving over time.

---

# 3. Project Objectives

The system must provide:

1. A professional trading dashboard.
2. A complete trade journal.
3. Screenshot-based trade review.
4. Strategy categorization and strategy analytics.
5. Trading calendar.
6. Trading account growth tracking.
7. Drawdown and risk analysis.
8. Mistake tracking.
9. Psychology tracking.
10. Trade execution scoring.
11. Setup grading.
12. Strategy playbooks.
13. Historical setup analysis.
14. Advanced reporting.
15. Goal and milestone tracking.
16. Multi-account support.
17. Search, filtering, export, and data backup capabilities.

---

# 4. Scope

## 4.1 In Scope

The initial system shall support:

- Manual trade entry.
- Multiple trading accounts.
- Forex, crypto, indices, commodities, and custom symbols.
- Strategy management.
- Trade setup categorization.
- Trade screenshots.
- Before/during/after journal notes.
- Win/loss/breakeven classification.
- Account balance tracking.
- Equity growth tracking.
- Daily and overall drawdown.
- Trade calendar.
- Trading sessions.
- Trade grading.
- Mistake tracking.
- Psychology tracking.
- Tags.
- Trading rules.
- Strategy checklists.
- Reports.
- CSV export.
- Image upload/storage.
- Responsive desktop/tablet/mobile UI.
- Dark and light themes.

## 4.2 Future Scope

Possible later integrations:

- MetaTrader 4 trade import.
- MetaTrader 5 trade import.
- cTrader trade import.
- TradingView webhook import.
- Broker API synchronization.
- Prop firm account synchronization.
- AI-assisted journal summaries.
- Automated screenshot analysis.
- AI pattern detection.
- Mobile application.
- Team/mentor review.
- Public shareable trade reports.
- Backtesting module.

These are not required for Version 1 unless explicitly added later.

---

# 5. User Roles

## 5.1 Trader

Primary user.

Permissions:

- Manage trading accounts.
- Create, edit, delete, and review trades.
- Upload screenshots.
- Manage strategies.
- Manage setup categories.
- Manage mistakes.
- Manage tags.
- Manage playbooks.
- View analytics.
- View reports.
- Configure trading rules.
- Configure personal goals.
- Export journal data.

## 5.2 Administrator

Optional role if the system becomes multi-user.

Permissions:

- Manage users.
- Manage global settings.
- View audit logs.
- Configure storage.
- Manage system-wide defaults.

For a single-user personal system, the Trader can be treated as the administrator.

---

# 6. Core Navigation

Recommended application sidebar:

```text
Dashboard

Trading
├── Add Trade
├── Trade Journal
├── Trade Calendar
└── Screenshots

Analytics
├── Overview
├── Strategies
├── Pairs
├── Sessions
├── Weekdays
├── Setup Grades
├── Mistakes
├── Psychology
└── Risk Analysis

Performance
├── Account Growth
├── Equity Curve
├── Drawdown
├── Daily Performance
├── Monthly Performance
└── Goals

Playbook
├── Strategies
├── Setups
├── Trading Rules
└── Checklists

Reports

Accounts

Settings
```

---

# 7. Dashboard Requirements

## 7.1 Dashboard Summary Cards

The dashboard shall display configurable summary cards including:

- Current Account Balance.
- Starting Balance.
- Net Profit/Loss.
- Account Growth Percentage.
- Win Rate.
- Total Trades.
- Profit Factor.
- Expectancy.
- Average R.
- Average Winner.
- Average Loser.
- Best Trade.
- Worst Trade.
- Current Drawdown.
- Maximum Drawdown.
- Current Win Streak.
- Current Loss Streak.
- Best Win Streak.
- Best Trading Day.
- Worst Trading Day.
- Best Strategy.
- Best Pair.
- Best Session.

Example:

```text
Balance             $5,250
Starting Balance    $5,000
Growth              +5.00%
Net P&L             +$250
Win Rate            44.2%
Profit Factor       2.31
Average R           +1.17R
Max Drawdown        -4.20%
```

## 7.2 Equity Curve

The dashboard must contain an account equity chart.

Chart options:

- All time.
- This year.
- Last 6 months.
- Last 3 months.
- This month.
- This week.
- Custom period.

The chart shall show:

- Starting balance.
- Closing balance after each trade.
- High-water mark.
- Drawdown areas.
- Deposit/withdrawal adjustments if applicable.

## 7.3 Performance Summary

Display:

- Today.
- This week.
- This month.
- This quarter.
- This year.
- All time.

Metrics:

- Net P&L.
- Net R.
- Growth %.
- Trades.
- Win rate.
- Profit factor.

## 7.4 Milestones

The system should allow milestones such as:

- +2%.
- +5%.
- +10%.
- +20%.
- +50%.
- +100%.

Example:

```text
Current Growth: +6.5%

Next Goal: +10%

█████████████░░░░░░░
65%
```

Milestone configuration must be editable.

---

# 8. Trading Account Management

## 8.1 Account Types

Supported account types:

- Personal Live.
- Demo.
- Funded.
- Prop Evaluation.
- Prop Funded.
- Backtesting.
- Custom.

## 8.2 Account Fields

Each account shall include:

- Account Name.
- Account Type.
- Broker.
- Currency.
- Initial Balance.
- Current Balance.
- Current Equity.
- Account Created Date.
- Status.
- Notes.

Optional funded-account fields:

- Max Overall Drawdown %.
- Max Daily Drawdown %.
- Profit Target %.
- Minimum Trading Days.
- Maximum Trading Days.
- Payout Target.
- Consistency Rule.
- Drawdown Calculation Type.
- Daily Reset Time.
- Challenge Phase.

## 8.3 Account Adjustments

Support:

- Deposit.
- Withdrawal.
- Fee.
- Refund.
- Profit Split.
- Manual Balance Correction.

These must not be counted as trading profit or loss.

---

# 9. Trade Management

## 9.1 Trade Lifecycle

Trade statuses:

- Planned.
- Open.
- Closed.
- Cancelled.
- Invalidated.

For Version 1, manual closed trade journaling is mandatory.

## 9.2 Trade Fields

Each trade shall support:

### Identification

- Trade ID.
- Account.
- Trade Number.
- Trade Date.
- Entry Date/Time.
- Exit Date/Time.

### Market

- Asset Class.
- Symbol.
- Pair.
- Market.
- Broker symbol if different.

Asset classes:

- Forex.
- Crypto.
- Indices.
- Commodities.
- Stocks.
- Futures.
- Custom.

### Direction

- Buy / Long.
- Sell / Short.

### Price Data

- Planned Entry.
- Actual Entry.
- Stop Loss.
- Planned Take Profit.
- Actual Exit.
- Optional partial exits.

### Position Data

- Lot Size.
- Quantity.
- Contract Size.
- Leverage.
- Tick/Pip Value where applicable.

### Risk

- Risk Amount.
- Risk Percentage.
- Planned Risk.
- Actual Risk.

### Results

- Gross P&L.
- Fees.
- Commission.
- Swap.
- Net P&L.
- Net P&L %.
- Planned RR.
- Achieved RR.
- R-Multiple.
- Outcome.

Outcome options:

- Win.
- Loss.
- Breakeven.

### Context

- Strategy.
- Setup.
- Session.
- Market Condition.
- Entry Model.
- Timeframe.
- Higher Timeframe Bias.
- Setup Grade.
- Tags.

---

# 10. Trade Strategy Categorization

Each trade must allow selecting a strategy from a dropdown.

Example strategies:

- Liquidity Sweep + MSS.
- Order Block.
- Breaker Block.
- FVG Continuation.
- Support / Resistance.
- London Sweep.
- New York Reversal.
- Trend Continuation.
- Custom strategy.

Strategies must be fully user-managed.

The user must be able to:

- Create strategy.
- Edit strategy.
- Archive strategy.
- Delete unused strategy.
- Add description.
- Add rules.
- Add preferred sessions.
- Add preferred pairs.
- Add minimum RR.
- Add screenshots/examples.
- Add checklist.

---

# 11. Setup Categorization

Strategies may contain one or multiple setups.

Example:

```text
Strategy: Liquidity Sweep + MSS

Setups:
- Asia Low Sweep
- Asia High Sweep
- Previous Day High Sweep
- Previous Day Low Sweep
- NY Continuation
```

Each setup shall maintain independent analytics.

---

# 12. Supporting Trade Categories

## 12.1 Entry Models

User-configurable values such as:

- FVG.
- Order Block.
- Breaker.
- Retest.
- Fibonacci.
- Market Structure Shift.
- Support/Resistance.
- Custom.

## 12.2 Market Conditions

Options:

- Trending.
- Ranging.
- Reversal.
- Consolidation.
- High Volatility.
- Low Volatility.
- News-driven.
- Custom.

## 12.3 Sessions

Default:

- Asia.
- London.
- New York.
- London/New York Overlap.
- Outside Session.
- Custom.

## 12.4 Higher-Timeframe Bias

Options:

- Bullish.
- Bearish.
- Neutral.
- Mixed.

---

# 13. Trade Setup Grades

Each trade may be graded before execution.

Default grades:

- A+.
- A.
- B.
- C.

Grade definitions must be configurable.

Example:

### A+

- All confirmations present.
- Aligned with HTF.
- Correct session.
- Clear liquidity.
- Required entry model.
- Minimum RR satisfied.

### A

- Strong setup.
- One minor weakness.

### B

- One important confirmation missing.

### C

- Low-quality or discretionary setup.

Analytics shall compare performance by setup grade.

Example:

| Grade | Trades | Win Rate | Net R | Profit Factor |
|---|---:|---:|---:|---:|
| A+ | 34 | 61% | +52R | 3.8 |
| A | 47 | 49% | +32R | 2.4 |
| B | 38 | 31% | -4R | 0.9 |
| C | 21 | 14% | -17R | 0.4 |

---

# 14. Trade Screenshots

## 14.1 Screenshot Types

Each trade may contain multiple screenshots under:

### Before Trade

Examples:

- Higher timeframe analysis.
- Liquidity map.
- Entry setup.
- Planned execution.

### During Trade

Examples:

- Price reaction.
- Partial profit.
- Stop adjustment.
- Management decision.

### After Trade

Examples:

- Final result.
- Missed target.
- Stop-out analysis.
- Post-trade markup.

## 14.2 Screenshot Fields

Each screenshot:

- Image.
- Type.
- Caption.
- Uploaded At.
- Sort Order.
- Optional timeframe.
- Optional annotation note.

## 14.3 Storage

Recommended storage:

Development:

- Laravel local/public disk.

Production:

- S3-compatible storage.
- Cloudflare R2.
- Amazon S3.
- DigitalOcean Spaces.

The backend should use Laravel Storage abstraction so providers can be changed.

---

# 15. Trade Journal

Each trade shall contain a structured journal.

## 15.1 Before Trade Journal

Fields:

- Why am I taking this trade?
- What is my HTF bias?
- What liquidity am I targeting?
- What invalidates the setup?
- Why is this entry valid?
- What is the planned management?
- Confidence score.

## 15.2 During Trade Journal

Fields:

- What is happening?
- Did market conditions change?
- Did I alter my plan?
- Why did I alter it?

## 15.3 After Trade Journal

Required questions:

- What happened?
- What did I do well?
- What did I do wrong?
- What did I learn?
- Would I take this trade again?
- Did I follow my plan?
- What will I improve next time?

Optional rating:

- Execution Score.
- Discipline Score.
- Patience Score.
- Emotional Control Score.

Each score: 1–10.

---

# 16. Mistake Tracking

## 16.1 Mistake Categories

Default examples:

- Entered early.
- Entered late.
- No confirmation.
- Over-risked.
- Moved stop loss.
- Closed early.
- Revenge trade.
- FOMO.
- Wrong session.
- Ignored HTF direction.
- Traded during news.
- Poor RR.
- Low-quality setup.
- Broke daily loss rule.
- Overtrading.
- Missed entry because of hesitation.
- Improper position size.
- Changed target emotionally.
- Added to losing trade.
- Custom.

## 16.2 Multiple Mistakes Per Trade

A trade may contain multiple mistake categories.

Each selected mistake may contain:

- Severity.
- Notes.
- Estimated Cost in R.
- Estimated Cost in money.

## 16.3 Mistake Analytics

The system shall identify:

- Most common mistake.
- Most expensive mistake.
- Mistake with highest loss rate.
- Mistake frequency by month.
- Mistake frequency by strategy.
- Mistake frequency by emotional state.
- Estimated R lost due to mistakes.

Example:

```text
Most Expensive Mistake

Entered Early

Occurrences: 18
Losses: 11
Estimated Cost: -6.4R
```

---

# 17. Psychology Tracking

## 17.1 Before Trade Emotion

User-selectable states:

- Calm.
- Confident.
- Nervous.
- Fearful.
- FOMO.
- Angry.
- Revenge mindset.
- Tired.
- Distracted.
- Overconfident.
- Neutral.
- Custom.

## 17.2 Ratings

Before trade:

- Confidence: 1–5.
- Focus: 1–5.
- Energy: 1–5.
- Stress: 1–5.

After trade:

- Discipline: 1–10.
- Execution: 1–10.
- Emotional Control: 1–10.
- Patience: 1–10.

## 17.3 Psychology Analytics

Reports shall show:

- Win rate by emotional state.
- Net R by emotional state.
- Average discipline score.
- Average execution score.
- Performance when calm.
- Performance when tired.
- Performance during FOMO.
- Performance after previous loss.
- Performance after previous win.

---

# 18. Planned vs Actual Execution

The system shall compare the planned trade against actual execution.

Example:

```text
Planned

Entry: 1.08450
SL: 1.08350
TP: 1.08950
RR: 1:5
Risk: 0.50%

Actual

Entry: 1.08480
Exit: 1.08720
Actual R: +1.85R
```

Metrics:

- Entry Deviation.
- Stop Deviation.
- Target Deviation.
- Planned RR.
- Achieved R.
- Execution Efficiency.
- Profit Capture Efficiency.

Suggested formula:

```text
Profit Capture Efficiency =
Actual Positive R / Planned Positive R × 100
```

Cap at a sensible range for display or allow values above 100% if exits outperform the original plan.

---

# 19. Trading Calendar

## 19.1 Calendar Views

Support:

- Monthly.
- Weekly.
- Daily.

## 19.2 Calendar Cell Data

Each date shall show:

- Net P&L.
- Net P&L %.
- Net R.
- Number of trades.
- Wins.
- Losses.
- Breakevens.

Positive days shall use positive-state styling.

Negative days shall use negative-state styling.

No-trade days shall use neutral styling.

## 19.3 Day Detail

Clicking a date shall open:

- All trades that day.
- Daily summary.
- Best trade.
- Worst trade.
- Win rate.
- Net P&L.
- Net R.
- Mistakes.
- Notes.

## 19.4 Monthly Header

Example:

```text
August 2026

Net P&L: +$420
Growth: +8.4%
Net R: +18.7R
Trades: 32
Wins: 15
Losses: 17
Win Rate: 46.9%
```

---

# 20. Strategy Playbook

## 20.1 Strategy Profile

Each strategy page shall support:

- Name.
- Description.
- Status.
- Preferred markets.
- Preferred pairs.
- Preferred sessions.
- Preferred timeframes.
- Minimum RR.
- Maximum risk.
- Required confirmations.
- Invalidation conditions.
- Entry model.
- Trade management rules.
- Example screenshots.
- Notes.

## 20.2 Strategy Checklist

Example:

```text
[ ] HTF bias defined
[ ] Liquidity identified
[ ] Liquidity sweep completed
[ ] Displacement confirmed
[ ] Market structure shift confirmed
[ ] Entry model confirmed
[ ] Minimum RR available
[ ] No conflicting major news
[ ] Risk within plan
```

Checklist items shall be user-configurable.

## 20.3 Trade Checklist Snapshot

When a trade is saved, the checklist state must be stored as a snapshot.

This prevents later strategy edits from modifying historical trade records.

## 20.4 Strategy Historical Performance

Display:

- Number of trades.
- Wins.
- Losses.
- Breakevens.
- Win rate.
- Net P&L.
- Net R.
- Average R.
- Average winner.
- Average loser.
- Profit factor.
- Expectancy.
- Maximum drawdown.
- Best pair.
- Best session.
- Best weekday.
- Best setup grade.

---

# 21. Historical Setup Score

The system shall not label future trades with guaranteed win probability.

Instead, it shall calculate a historical setup quality score.

## 21.1 Score Name

Recommended:

**Setup Quality Score**

Range:

```text
0–100
```

## 21.2 Example Factors

Configurable weighted factors:

- HTF alignment.
- Strategy checklist completion.
- Correct trading session.
- Minimum RR achieved.
- Liquidity condition.
- Entry confirmation.
- Market condition suitability.
- News avoidance.
- User-defined confirmations.

## 21.3 Historical Similarity Summary

Display:

```text
Setup Quality Score: 87 / 100

Similar Historical Trades: 63
Historical Win Rate: 57.1%
Average Result: +1.92R
Profit Factor: 2.64
```

This must be clearly presented as historical information, not guaranteed future probability.

---

# 22. Performance Analytics

## 22.1 Required Metrics

The system shall calculate:

- Total Trades.
- Wins.
- Losses.
- Breakevens.
- Win Rate.
- Loss Rate.
- Breakeven Rate.
- Gross Profit.
- Gross Loss.
- Net Profit.
- Net Profit %.
- Average Winner.
- Average Loser.
- Largest Winner.
- Largest Loser.
- Average R.
- Total R.
- Profit Factor.
- Expectancy.
- Risk/Reward Ratio.
- Current Win Streak.
- Current Loss Streak.
- Longest Win Streak.
- Longest Loss Streak.
- Maximum Drawdown.
- Current Drawdown.
- Recovery Factor.
- Average Holding Time.
- Average Winning Hold Time.
- Average Losing Hold Time.

---

# 23. Metric Formulas

## 23.1 Win Rate

```text
Win Rate =
Wins / (Wins + Losses) × 100
```

Breakeven trades may be excluded from standard win-rate calculation.

## 23.2 Profit Factor

```text
Profit Factor =
Gross Profit / Absolute Gross Loss
```

## 23.3 Average Winner

```text
Average Winner =
Gross Profit / Number of Winning Trades
```

## 23.4 Average Loser

```text
Average Loser =
Gross Loss / Number of Losing Trades
```

Display loser as a positive magnitude or clearly mark it negative.

## 23.5 R-Multiple

```text
R-Multiple =
Net Trade P&L / Initial Risk Amount
```

Example:

```text
Risk = $50
Profit = $250

R = +5R
```

## 23.6 Expectancy in R

```text
Expectancy =
(Win Rate × Average Winning R)
-
(Loss Rate × Average Losing R)
```

Rates must be converted to decimal form.

## 23.7 Growth Percentage

```text
Growth % =
(Current Adjusted Equity - Initial Balance - Net Deposits)
/
Initial Balance
× 100
```

The exact formula may be adapted when deposits and withdrawals occur.

## 23.8 Drawdown

```text
Drawdown =
(Current Equity - Previous Equity Peak)
/
Previous Equity Peak
× 100
```

## 23.9 Maximum Drawdown

Maximum observed peak-to-trough percentage decline.

---

# 24. Strategy Analytics

The Strategies Analytics page shall rank strategies using configurable metrics.

Table columns:

- Strategy.
- Total Trades.
- Wins.
- Losses.
- Win Rate.
- Avg R.
- Net R.
- Net P&L.
- Profit Factor.
- Expectancy.
- Max Drawdown.
- Sample Confidence.

## 24.1 Sample Confidence

Suggested labels:

- Low Sample.
- Developing.
- Moderate.
- Reliable.

Example configuration:

```text
1–9 trades     Low Sample
10–29 trades   Developing
30–49 trades   Moderate
50+ trades     Reliable
```

Thresholds must be configurable.

This prevents strategies with very few trades from appearing falsely superior.

---

# 25. Pair Analytics

The system shall produce analytics per symbol/pair.

Metrics:

- Trades.
- Win rate.
- Net P&L.
- Net R.
- Profit factor.
- Expectancy.
- Average RR.
- Best strategy on the pair.
- Best session.
- Maximum drawdown.

Example:

```text
Best Pair: XAUUSD

Trades: 48
Win Rate: 50.0%
Net R: +36R
Profit Factor: 2.9
```

---

# 26. Session Analytics

The system shall compare:

- Asia.
- London.
- New York.
- London/NY overlap.
- Custom sessions.

Metrics:

- Trade count.
- Win rate.
- Net R.
- Net P&L.
- Profit factor.
- Average R.
- Expectancy.

---

# 27. Weekday Analytics

Compare:

- Monday.
- Tuesday.
- Wednesday.
- Thursday.
- Friday.
- Saturday.
- Sunday.

Charts:

- Net R by weekday.
- Win rate by weekday.
- Trade count by weekday.

---

# 28. Time-of-Day Analytics

Optional but recommended.

Group trades by:

- Hour.
- 30-minute period.
- Session window.

Example:

```text
08:00–09:00
09:00–10:00
10:00–11:00
```

This can identify the trader's highest-performing execution window.

---

# 29. Risk Analytics

The Risk Analysis page shall display:

- Average risk %.
- Highest risk trade.
- Lowest risk trade.
- Average risk on wins.
- Average risk on losses.
- Risk by strategy.
- Risk by setup grade.
- Risk consistency.
- Daily risk used.
- Weekly risk used.
- Consecutive-loss risk exposure.

For funded accounts:

- Current daily drawdown.
- Remaining daily drawdown buffer.
- Current overall drawdown.
- Remaining overall drawdown buffer.

---

# 30. Funded Account Risk Panel

Example:

```text
Account Balance:            $5,325
Starting Balance:           $5,000
Growth:                     +6.50%

Max Overall Drawdown:       10.00%
Current Drawdown:           1.57%
Remaining DD Buffer:        8.43%

Daily Drawdown Limit:       5.00%
Today's Drawdown:           0.50%
Remaining Daily Buffer:     4.50%
```

The system must visually warn when thresholds are approached.

Suggested warnings:

- 50% limit used.
- 75% limit used.
- 90% limit used.
- Limit exceeded.

---

# 31. Goals

Users shall be able to create goals.

Goal types:

- Account Balance.
- Growth Percentage.
- Net R.
- Maximum Drawdown.
- Discipline Score.
- Monthly Profit.
- Weekly Profit.
- Minimum Number of A+ Trades.
- Maximum Number of Rule Violations.

Fields:

- Goal name.
- Type.
- Target value.
- Start date.
- End date.
- Status.
- Current progress.

---

# 32. Trading Rules

The Playbook shall contain trading rules.

Example:

```text
Maximum risk per trade: 0.5%
Maximum daily loss: 2%
Maximum 3 trades per day
No revenge trading
No new trade after daily limit reached
Minimum RR: 1:3
Only A+ and A setups
```

Rules should support:

- Text.
- Category.
- Severity.
- Active/inactive.
- Optional automatic validation.

---

# 33. Rule Violation Tracking

When journaling, user can mark rules violated.

Examples:

- Exceeded max risk.
- Exceeded daily trade count.
- Took below minimum RR.
- Traded outside session.
- Took C-grade setup.
- Entered without confirmation.

Analytics shall show:

- Rule violations per month.
- P&L on rule-following trades.
- P&L on rule-breaking trades.
- Net R lost due to rule violations.

This is a high-value feature.

Example:

```text
Trades Following Rules
Net R: +42.8R

Trades Breaking Rules
Net R: -19.6R
```

---

# 34. Tags

Trades may contain unlimited tags.

Example:

- CPI.
- NFP.
- Sweep.
- Reversal.
- Continuation.
- Breakout.
- High confidence.
- Low confidence.
- News.
- Countertrend.

Tags must be searchable and filterable.

---

# 35. Trade Journal List

The Trade Journal page shall contain:

- Search.
- Advanced filters.
- Sort.
- Pagination.
- Table view.
- Card view.
- Screenshot preview.

Suggested table columns:

- Date.
- Pair.
- Direction.
- Strategy.
- Grade.
- Entry.
- Exit.
- Risk %.
- R.
- P&L.
- Outcome.
- Session.
- Mistakes.
- Journal status.

---

# 36. Trade Detail Page

The trade detail page should provide a full review experience.

Recommended layout:

```text
Header
├── Pair
├── Direction
├── Outcome
├── P&L
├── R
└── Setup Grade

Trade Summary

Planned vs Actual

Strategy + Setup

Checklist

Screenshots Timeline

Before Trade Journal

During Trade Journal

After Trade Journal

Mistakes

Psychology

Rule Violations

Lessons

Related Analytics
```

---

# 37. Reports

The system shall support reports with filters.

## 37.1 Filter Options

- Account.
- Date range.
- Pair.
- Asset class.
- Strategy.
- Setup.
- Session.
- Direction.
- Market condition.
- Entry model.
- Setup grade.
- Outcome.
- Mistake.
- Psychology.
- Tag.
- Minimum/maximum risk.
- Minimum/maximum R.
- Weekday.
- Time period.

## 37.2 Report Types

### Performance Overview

- Net P&L.
- Net R.
- Win Rate.
- Profit Factor.
- Expectancy.
- Drawdown.

### Strategy Report

### Pair Report

### Session Report

### Weekday Report

### Setup Grade Report

### Mistake Report

### Psychology Report

### Risk Report

### Rule Compliance Report

### Account Growth Report

### Monthly Review Report

---

# 38. Monthly Review

The system should automatically prepare a monthly review.

Example sections:

- Starting Balance.
- Ending Balance.
- Net Profit/Loss.
- Growth %.
- Net R.
- Total Trades.
- Wins.
- Losses.
- Win Rate.
- Profit Factor.
- Best Strategy.
- Worst Strategy.
- Best Pair.
- Worst Pair.
- Best Session.
- Most Common Mistake.
- Most Expensive Mistake.
- Average Discipline Score.
- Rule Compliance %.
- Best Trade.
- Worst Trade.
- Key Lessons.
- Next Month Focus.

The user may add a manual monthly reflection.

---

# 39. High-Probability Historical Report

The user shall be able to search combinations of historical conditions.

Example query:

```text
Strategy:
Liquidity Sweep + MSS

Pair:
XAUUSD

Session:
New York

Grade:
A+

HTF Bias:
Aligned

Minimum RR:
1:3
```

The system returns:

```text
Historical Matches: 42

Wins: 26
Losses: 16

Historical Win Rate: 61.9%
Average R: +1.84R
Profit Factor: 3.10
Net R: +77.3R
```

The UI must call this:

**Historical Performance**

or:

**Historical Setup Statistics**

It should not present the value as a guaranteed future win probability.

---

# 40. Analytics Comparison Engine

Users should be able to compare two segments.

Examples:

- A+ vs B setups.
- London vs New York.
- XAUUSD vs EURUSD.
- Strategy A vs Strategy B.
- Calm vs FOMO.
- Rule-following vs rule-breaking trades.
- 0.5% risk vs 1% risk.
- Monday vs Friday.

Metrics compared:

- Total trades.
- Win rate.
- Average R.
- Net R.
- Profit factor.
- Expectancy.
- Drawdown.
- Average discipline.

---

# 41. Search

Global search should support:

- Trade ID.
- Pair.
- Strategy.
- Journal text.
- Mistake.
- Tag.
- Account.
- Screenshot caption.

---

# 42. Notifications

Optional internal notifications:

- Daily loss threshold approaching.
- Overall drawdown threshold approaching.
- Profit target reached.
- Monthly review ready.
- Weekly review ready.
- Goal achieved.
- Strategy reaches minimum sample size.
- Repeated mistake detected.

---

# 43. Export

Users shall be able to export:

- Trades CSV.
- Trades Excel.
- Strategy report CSV.
- Monthly report.
- Account history.
- Mistakes report.
- Performance report.

PDF export may be added later.

---

# 44. Frontend Technical Architecture

## 44.1 Technology Stack

Frontend:

- Next.js.
- TypeScript.
- React.
- shadcn/ui.
- Tailwind CSS.
- TanStack Query.
- React Hook Form.
- Zod.
- Recharts or another production-ready React chart library.
- Lucide React.
- date-fns.
- Optional Zustand for small UI-only client state.

## 44.2 Rendering Strategy

Recommended:

- Next.js App Router.
- Server Components where beneficial.
- Client Components for:
  - Interactive charts.
  - Complex forms.
  - Calendar.
  - Filters.
  - Upload interfaces.
  - Live calculations.

## 44.3 API Communication

Recommended:

```text
Frontend
    ↓
Typed API Client
    ↓
Laravel REST API
```

Use a centralized API layer.

Example:

```text
src/
├── app/
├── components/
├── features/
├── lib/
│   ├── api/
│   ├── auth/
│   ├── utils/
│   └── validation/
├── hooks/
├── types/
└── constants/
```

## 44.4 Feature-Based Frontend Structure

Recommended:

```text
src/features/
├── dashboard/
├── trades/
├── accounts/
├── strategies/
├── analytics/
├── calendar/
├── playbook/
├── mistakes/
├── psychology/
├── reports/
├── goals/
└── settings/
```

---

# 45. UI/UX Requirements

## 45.1 Visual Direction

The interface should feel like a premium professional trading analytics platform.

Design characteristics:

- Clean.
- Data-dense but readable.
- Strong typography.
- Minimal unnecessary decoration.
- Dark-mode optimized.
- Professional charts.
- Clear positive/negative states.
- Compact dashboard cards.
- Responsive tables.
- Fast filter interactions.

## 45.2 Theme

Must support:

- Dark mode.
- Light mode.
- System preference.

## 45.3 Desktop First

Primary use case is desktop trading review.

However, all pages must remain usable on:

- Tablet.
- Mobile.

## 45.4 Positive / Negative Styling

Positive results:

- Success semantic token.

Negative results:

- Danger semantic token.

Breakeven:

- Neutral token.

Do not hardcode colors throughout components. Use theme tokens.

---

# 46. Dashboard Page Layout

Recommended desktop layout:

```text
Top Navigation / Account Selector

KPI Cards Row

Large Equity Curve
| Account Growth Card |

Performance Summary
| Win/Loss Distribution |

Recent Trades
| Best Strategy |

Trading Calendar Preview

Mistake Insight
| Psychology Insight |

Goals / Milestones
```

---

# 47. Backend Technical Architecture

## 47.1 Backend Stack

- Laravel 13.
- PHP 8.4+ recommended.
- Laravel Sanctum.
- Laravel Queues.
- Laravel Scheduler.
- Laravel Storage.
- Laravel Notifications where useful.
- MySQL 8+ or PostgreSQL 16+.
- Redis recommended for:
  - Queue.
  - Cache.
  - Rate limiting.
  - Expensive analytics cache.

## 47.2 Architecture Style

Recommended:

```text
Controller
    ↓
Form Request
    ↓
Service / Action
    ↓
Domain / Query Logic
    ↓
Model / Repository if required
```

Avoid placing complex analytics inside controllers.

Recommended directories:

```text
app/
├── Actions/
├── Analytics/
├── DTOs/
├── Enums/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Policies/
├── Services/
├── Support/
└── Jobs/
```

---

# 48. Authentication

Recommended:

Laravel Sanctum.

Supported flow:

```text
Next.js
  ↓
Laravel Authentication API
  ↓
Sanctum
```

System should support:

- Login.
- Logout.
- Current user.
- Password reset.
- Email verification if multi-user.
- Session revoke.

If deployed on separate subdomains, configure:

- CORS.
- Stateful domains.
- CSRF.
- Secure cookies.

---

# 49. Authorization

Laravel Policies should protect:

- Accounts.
- Trades.
- Strategies.
- Reports.
- Screenshots.
- Playbooks.
- Goals.

Users may access only their own trading data unless administrator access is explicitly configured.

---

# 50. Database Design

Recommended entities:

```text
users

trading_accounts
account_transactions
account_balance_snapshots

trades
trade_partial_exits
trade_screenshots
trade_journals

strategies
strategy_setups
strategy_rules
strategy_checklist_items
trade_checklist_snapshots

sessions
market_conditions
entry_models
setup_grades

mistake_categories
trade_mistakes

psychology_states
trade_psychology

trading_rules
trade_rule_violations

tags
trade_tag

goals
goal_progress

monthly_reviews

analytics_snapshots

audit_logs
```

---

# 51. Users Table

Suggested fields:

```text
id
name
email
email_verified_at
password
timezone
default_currency
theme
created_at
updated_at
```

---

# 52. Trading Accounts Table

```text
id
user_id
name
account_type
broker
currency
initial_balance
current_balance
status
max_overall_drawdown_percent
max_daily_drawdown_percent
profit_target_percent
daily_reset_time
notes
created_at
updated_at
```

---

# 53. Account Transactions Table

```text
id
trading_account_id
type
amount
transaction_date
notes
created_at
updated_at
```

Types:

- deposit.
- withdrawal.
- fee.
- refund.
- profit_split.
- adjustment.

---

# 54. Trades Table

Suggested core fields:

```text
id
uuid
user_id
trading_account_id

trade_date
entry_at
exit_at

asset_class
symbol
direction

planned_entry_price
actual_entry_price
stop_loss_price
planned_take_profit_price
actual_exit_price

position_size
quantity

planned_risk_amount
actual_risk_amount
risk_percent

gross_profit_loss
fees
commission
swap
net_profit_loss
net_profit_loss_percent

planned_rr
r_multiple

outcome

strategy_id
strategy_setup_id
session_id
market_condition_id
entry_model_id
setup_grade_id

timeframe
higher_timeframe_bias

status

followed_plan
would_take_again

execution_score
discipline_score
patience_score
emotional_control_score

created_at
updated_at
deleted_at
```

Use soft deletes for trades.

---

# 55. Trade Partial Exits Table

```text
id
trade_id
exit_price
quantity
percentage_closed
profit_loss
r_multiple
exited_at
notes
```

---

# 56. Trade Screenshots Table

```text
id
trade_id
type
storage_disk
storage_path
caption
timeframe
sort_order
created_at
updated_at
```

Types:

- before.
- during.
- after.

---

# 57. Trade Journals Table

```text
id
trade_id

before_trade_reason
htf_bias_notes
liquidity_target
setup_invalidation
entry_reason
planned_management

during_trade_notes

after_trade_summary
what_went_well
what_went_wrong
lesson_learned
next_time_improvement

monthly_review_reference

created_at
updated_at
```

---

# 58. Strategies Table

```text
id
user_id
name
slug
description
status
minimum_rr
maximum_risk_percent
preferred_timeframes
notes
created_at
updated_at
deleted_at
```

---

# 59. Strategy Setups Table

```text
id
strategy_id
name
description
status
created_at
updated_at
```

---

# 60. Strategy Checklist Items Table

```text
id
strategy_id
label
description
weight
is_required
sort_order
created_at
updated_at
```

---

# 61. Trade Checklist Snapshots Table

```text
id
trade_id
strategy_checklist_item_id
label_snapshot
weight_snapshot
is_required_snapshot
is_checked
created_at
```

---

# 62. Mistake Categories Table

```text
id
user_id
name
description
severity_default
status
created_at
updated_at
```

---

# 63. Trade Mistakes Table

```text
id
trade_id
mistake_category_id
severity
estimated_cost_r
estimated_cost_amount
notes
created_at
updated_at
```

---

# 64. Psychology States Table

```text
id
user_id
name
status
created_at
updated_at
```

---

# 65. Trade Psychology Table

```text
id
trade_id

before_emotion_id

confidence_score
focus_score
energy_score
stress_score

discipline_score
execution_score
emotional_control_score
patience_score

notes

created_at
updated_at
```

---

# 66. Trading Rules Table

```text
id
user_id
name
description
category
severity
is_active
created_at
updated_at
```

---

# 67. Trade Rule Violations Table

```text
id
trade_id
trading_rule_id
notes
estimated_cost_r
created_at
```

---

# 68. Tags

## tags

```text
id
user_id
name
slug
created_at
updated_at
```

## trade_tag

```text
trade_id
tag_id
```

---

# 69. Setup Grades Table

```text
id
user_id
name
description
score_min
score_max
sort_order
created_at
updated_at
```

Defaults:

- A+.
- A.
- B.
- C.

---

# 70. Goals Table

```text
id
user_id
trading_account_id
name
goal_type
target_value
start_value
current_value
start_date
target_date
status
created_at
updated_at
```

---

# 71. Monthly Reviews Table

```text
id
user_id
trading_account_id
year
month

starting_balance
ending_balance
net_profit_loss
growth_percent
net_r
trade_count
wins
losses
breakevens
win_rate
profit_factor

best_strategy_id
worst_strategy_id

key_lessons
next_month_focus
manual_notes

created_at
updated_at
```

---

# 72. Audit Log

Optional but recommended.

Track:

- Trade created.
- Trade edited.
- Trade deleted.
- Balance manually adjusted.
- Strategy changed.
- Account settings changed.

Fields:

```text
id
user_id
event
auditable_type
auditable_id
old_values
new_values
ip_address
created_at
```

---

# 73. API Design

Base URL example:

```text
/api/v1
```

---

# 74. Authentication API

```text
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
GET    /api/v1/auth/user
POST   /api/v1/auth/forgot-password
POST   /api/v1/auth/reset-password
```

---

# 75. Trading Accounts API

```text
GET    /api/v1/accounts
POST   /api/v1/accounts
GET    /api/v1/accounts/{account}
PUT    /api/v1/accounts/{account}
DELETE /api/v1/accounts/{account}

GET    /api/v1/accounts/{account}/transactions
POST   /api/v1/accounts/{account}/transactions

GET    /api/v1/accounts/{account}/growth
GET    /api/v1/accounts/{account}/drawdown
```

---

# 76. Trades API

```text
GET    /api/v1/trades
POST   /api/v1/trades
GET    /api/v1/trades/{trade}
PUT    /api/v1/trades/{trade}
DELETE /api/v1/trades/{trade}

POST   /api/v1/trades/{trade}/close
POST   /api/v1/trades/{trade}/restore
```

Filters should be accepted as query parameters.

---

# 77. Screenshot API

```text
GET    /api/v1/trades/{trade}/screenshots
POST   /api/v1/trades/{trade}/screenshots
PUT    /api/v1/trades/{trade}/screenshots/{screenshot}
DELETE /api/v1/trades/{trade}/screenshots/{screenshot}
```

Support multipart uploads.

---

# 78. Strategy API

```text
GET    /api/v1/strategies
POST   /api/v1/strategies
GET    /api/v1/strategies/{strategy}
PUT    /api/v1/strategies/{strategy}
DELETE /api/v1/strategies/{strategy}

GET    /api/v1/strategies/{strategy}/setups
POST   /api/v1/strategies/{strategy}/setups

GET    /api/v1/strategies/{strategy}/checklist
POST   /api/v1/strategies/{strategy}/checklist
```

---

# 79. Analytics API

```text
GET /api/v1/analytics/overview
GET /api/v1/analytics/equity-curve
GET /api/v1/analytics/drawdown
GET /api/v1/analytics/strategies
GET /api/v1/analytics/pairs
GET /api/v1/analytics/sessions
GET /api/v1/analytics/weekdays
GET /api/v1/analytics/setup-grades
GET /api/v1/analytics/mistakes
GET /api/v1/analytics/psychology
GET /api/v1/analytics/risk
GET /api/v1/analytics/rule-compliance
GET /api/v1/analytics/historical-setup
GET /api/v1/analytics/compare
```

Common parameters:

```text
account_id
date_from
date_to
strategy_id
symbol
session_id
setup_grade_id
outcome
```

---

# 80. Calendar API

```text
GET /api/v1/calendar/month
GET /api/v1/calendar/day/{date}
```

Example:

```text
GET /api/v1/calendar/month?account_id=1&year=2026&month=8
```

---

# 81. Reports API

```text
GET  /api/v1/reports/performance
GET  /api/v1/reports/strategies
GET  /api/v1/reports/pairs
GET  /api/v1/reports/mistakes
GET  /api/v1/reports/psychology
GET  /api/v1/reports/monthly
POST /api/v1/reports/export
```

---

# 82. Dashboard API

Recommended consolidated endpoint:

```text
GET /api/v1/dashboard
```

Returns:

- Account summary.
- KPI cards.
- Equity curve.
- Recent trades.
- Monthly performance.
- Best strategy.
- Best pair.
- Current goals.
- Drawdown status.
- Calendar preview.
- Mistake insight.

This reduces many frontend requests.

---

# 83. Analytics Query Architecture

Analytics should not be duplicated across controllers.

Recommended classes:

```text
app/Analytics/
├── PerformanceMetricsCalculator.php
├── EquityCurveCalculator.php
├── DrawdownCalculator.php
├── StrategyAnalytics.php
├── PairAnalytics.php
├── SessionAnalytics.php
├── WeekdayAnalytics.php
├── MistakeAnalytics.php
├── PsychologyAnalytics.php
├── RiskAnalytics.php
├── RuleComplianceAnalytics.php
└── HistoricalSetupAnalyzer.php
```

---

# 84. Analytics Caching

For small personal datasets, calculations may initially be real-time.

As trade count grows, cache expensive analytics.

Recommended cache key pattern:

```text
analytics:{user_id}:{account_id}:{metric}:{filter_hash}
```

Cache invalidation events:

- Trade created.
- Trade updated.
- Trade deleted.
- Account transaction created.
- Strategy changed where relevant.

---

# 85. Background Jobs

Use queues for:

- Large exports.
- Screenshot image processing.
- Monthly review generation.
- Analytics snapshot generation.
- Large imports in future versions.

---

# 86. Scheduler

Laravel Scheduler can run:

Daily:

- Snapshot account metrics.
- Check goal progress.
- Generate daily performance summary.

Weekly:

- Prepare weekly review metrics.

Monthly:

- Generate monthly review snapshot.

---

# 87. Validation Requirements

Trade creation validation must ensure:

- Account exists.
- Account belongs to user.
- Symbol is present.
- Direction is valid.
- Risk cannot be negative.
- P&L values are numeric.
- Strategy belongs to user.
- Setup belongs to selected strategy.
- Exit date cannot precede entry date.
- Screenshot file type is allowed.
- Screenshot file size is within configured maximum.

---

# 88. Financial Precision

Prices and financial values must not use binary floating-point storage where precision matters.

Use:

```text
DECIMAL
```

Examples:

```text
DECIMAL(20,8) for prices
DECIMAL(20,4) for monetary values
DECIMAL(10,4) for percentages/R metrics
```

Exact precision may vary by asset type.

---

# 89. Timezone Requirements

Store timestamps in UTC.

Each user shall have a timezone.

Frontend displays dates in user timezone.

Trading session classification should use configurable trading timezone.

This is important for forex sessions and broker times.

---

# 90. Delete Behavior

Use soft deletes for:

- Trades.
- Strategies.
- Accounts where appropriate.

Uploaded images should not be permanently deleted immediately when a trade is soft-deleted.

Permanent deletion may be handled through a cleanup process.

---

# 91. Responsive Requirements

Desktop:

- Full sidebar.
- Multi-column analytics.
- Large charts.
- Data tables.

Tablet:

- Collapsible sidebar.
- Responsive grid.

Mobile:

- Drawer navigation.
- Stacked KPI cards.
- Horizontal-scroll tables where unavoidable.
- Mobile trade form.
- Mobile calendar.

---

# 92. Accessibility

Minimum requirements:

- Keyboard navigation.
- Proper labels.
- Visible focus states.
- Semantic HTML.
- Accessible dialogs.
- Accessible forms.
- Sufficient contrast.
- Do not communicate profit/loss using color alone.

---

# 93. Security

Required protections:

- Authentication.
- Authorization.
- CSRF protection.
- XSS prevention.
- SQL injection prevention via ORM/query binding.
- Rate limiting.
- Secure file validation.
- MIME-type validation.
- File size limit.
- Randomized storage filenames.
- Signed/private image access where required.
- HTTPS in production.
- Secure cookies.
- Environment secrets outside repository.

---

# 94. File Upload Security

Allowed image types:

- JPEG.
- PNG.
- WebP.

Optional:

- GIF disabled by default.

Maximum file size should be configurable.

Recommended initial limit:

```text
10 MB per screenshot
```

Generate optimized thumbnails for journal lists.

---

# 95. Data Privacy

The trading journal contains personal financial and performance information.

The system shall:

- Prevent cross-user access.
- Avoid exposing file paths publicly.
- Require authentication for private screenshots.
- Allow user export.
- Allow user deletion.
- Avoid logging sensitive credentials or account secrets.

Broker passwords or API secrets should not be stored in Version 1.

---

# 96. Performance Requirements

Recommended targets:

- Dashboard initial API response: under 1.5 seconds for normal datasets.
- Trade list: under 1 second.
- Trade save: under 1 second excluding large image upload.
- Calendar response: under 1 second.
- Cached analytics: under 500 ms where practical.

Pagination must be used for trade lists.

---

# 97. Scalability

The data model should support:

- 1 user with thousands of trades.
- Multiple accounts.
- Thousands of screenshots.
- Later multi-user SaaS conversion.

Avoid architectural decisions that assume only one account.

Every user-owned entity should include ownership directly or indirectly.

---

# 98. Empty States

Pages must provide professional empty states.

Examples:

Dashboard:

```text
No trades yet.
Add your first trade to start building your performance history.
```

Strategies:

```text
No strategy created.
Create your first trading strategy and start measuring its performance.
```

Calendar:

```text
No trading activity this month.
```

---

# 99. Error Handling

Frontend should handle:

- API errors.
- Validation errors.
- Upload failures.
- Network errors.
- Authentication expiry.
- Permission errors.

Use shadcn Toast/Sonner for non-blocking notifications.

Forms must display inline validation messages.

---

# 100. Loading States

Provide:

- Skeleton cards.
- Skeleton charts.
- Loading rows.
- Upload progress.
- Disabled save state.

Avoid full-screen loaders for normal page transitions.

---

# 101. Core Frontend Components

Recommended reusable components:

```text
AccountSelector
MetricCard
GrowthBadge
PnLValue
RMultipleBadge
OutcomeBadge
SetupGradeBadge
StrategyBadge
DrawdownProgress
EquityCurveChart
WinLossChart
MonthlyCalendar
TradeCard
TradeTable
ScreenshotTimeline
TradeJournalPanel
StrategyPerformanceTable
MistakeBreakdownChart
PsychologyPerformanceChart
GoalProgressCard
FilterBar
DateRangeFilter
AnalyticsComparison
```

---

# 102. Trade Form UX

Recommended multi-section single-page form:

```text
1. Account & Market

2. Trade Plan

3. Strategy & Setup

4. Risk

5. Execution

6. Result

7. Psychology

8. Mistakes

9. Journal

10. Screenshots
```

Use collapsible sections if the form becomes long.

Auto-calculate:

- Planned RR.
- Risk amount.
- Net P&L.
- P&L %.
- R-multiple.

Manual override should be possible if broker calculations differ.

---

# 103. Quick Add Trade

Provide an optional quick trade entry mode.

Required:

- Account.
- Symbol.
- Direction.
- Entry.
- SL.
- Exit.
- Risk.
- Result.
- Strategy.

The user can add the detailed journal later.

Display journal completeness:

```text
Journal Complete: 65%
```

---

# 104. Journal Completeness Score

Suggested completion fields:

- Strategy selected.
- Setup selected.
- Result entered.
- Screenshot before.
- Screenshot after.
- Post-trade lesson.
- Mistake review.
- Psychology review.

This helps prevent incomplete journaling.

---

# 105. Data Visualization Requirements

Charts should support tooltips and filters.

Recommended charts:

- Equity Curve.
- Drawdown Curve.
- Monthly P&L Bar Chart.
- Net R by Strategy.
- Win Rate by Strategy.
- Profit Factor by Strategy.
- Net R by Pair.
- Net R by Session.
- Weekday Performance.
- Mistake Frequency.
- Mistake Cost.
- Psychology vs Performance.
- Setup Grade Performance.
- Risk vs Result Scatter Plot.
- R Distribution Histogram.

---

# 106. Account Growth Page

Display:

- Starting balance.
- Current balance.
- Highest balance.
- Net trading profit.
- Growth %.
- Deposits.
- Withdrawals.
- Current drawdown.
- Maximum drawdown.
- Next target.
- Growth curve.

Milestone history:

```text
+2% reached
+5% reached
+10% reached
```

Store date achieved.

---

# 107. Drawdown Page

Display:

- Current drawdown.
- Maximum drawdown.
- Highest historical equity.
- Current equity.
- Recovery needed.
- Largest losing streak.
- Drawdown duration.
- Drawdown chart.

For funded accounts:

- Daily drawdown limit.
- Overall drawdown limit.
- Remaining buffer.

---

# 108. Weekly Review

Recommended auto-generated review:

```text
Week: Aug 3–Aug 9

Trades: 7
Wins: 3
Losses: 4
Win Rate: 42.9%
Net R: +5.6R
Growth: +1.8%

Best Strategy:
Liquidity Sweep + MSS

Biggest Mistake:
Early Entry

Rule Compliance:
86%

Focus Next Week:
Wait for full MSS confirmation.
```

---

# 109. Data Integrity Rules

Important:

- Closed trade metrics should be recalculated server-side.
- Financial calculations must not trust frontend-only values.
- Historical checklist snapshots must remain immutable.
- Deleting a strategy must not delete historical trades.
- Archived strategies remain visible in old trades.
- Account adjustments must be separate from trading P&L.
- Analytics must exclude deleted trades.
- Breakevens must be handled consistently.

---

# 110. Testing Requirements

## 110.1 Laravel

Use:

- PHPUnit or Pest.

Tests:

- Authentication.
- Account ownership.
- Trade CRUD.
- Strategy CRUD.
- Screenshot authorization.
- Analytics calculations.
- Win rate.
- Profit factor.
- Expectancy.
- R-multiple.
- Drawdown.
- Growth.
- Calendar aggregation.
- Filters.
- Rule compliance.
- Sample confidence.

## 110.2 Frontend

Use:

- Vitest.
- React Testing Library.
- Playwright for end-to-end tests.

Critical E2E flows:

1. Login.
2. Create trading account.
3. Create strategy.
4. Create trade.
5. Upload screenshot.
6. Journal trade.
7. Close trade.
8. View trade on calendar.
9. View strategy analytics.
10. Confirm dashboard metrics update.

---

# 111. Seed Data

Development seeders should create:

- Demo user.
- Demo account.
- Default strategies.
- Default sessions.
- Default mistakes.
- Default setup grades.
- Default psychology states.
- 50–100 sample trades.

This allows dashboard testing without manual entry.

---

# 112. Default System Seed Values

## Sessions

```text
Asia
London
New York
London/New York Overlap
```

## Setup Grades

```text
A+
A
B
C
```

## Mistakes

```text
Entered Early
Entered Late
No Confirmation
Over Risked
Moved Stop Loss
Closed Early
Revenge Trade
FOMO
Wrong Session
Ignored HTF Bias
Traded News
Poor RR
Low Quality Setup
Overtrading
```

## Psychology

```text
Calm
Confident
Nervous
Fearful
FOMO
Angry
Revenge
Tired
Distracted
Overconfident
Neutral
```

---

# 113. Non-Functional Requirements

The system shall be:

- Secure.
- Responsive.
- Maintainable.
- Modular.
- Testable.
- Scalable.
- Accessible.
- Fast.
- Easy to journal with.
- Visually professional.

---

# 114. Recommended Deployment Architecture

```text
Browser
   ↓
Next.js Frontend
   ↓
Laravel 13 API
   ↓
MySQL / PostgreSQL

Laravel
├── Redis
├── Queue Worker
├── Scheduler
└── Object Storage
```

Possible production setup:

```text
Cloudflare
   ↓
Nginx

Frontend:
Next.js

Backend:
Laravel 13 + PHP-FPM

Data:
MySQL/PostgreSQL

Cache/Queue:
Redis

Screenshots:
Cloudflare R2 / S3
```

---

# 115. Environment Separation

Required environments:

- Local.
- Staging.
- Production.

Each environment must use separate:

- Database.
- Storage.
- Credentials.
- API URLs.

---

# 116. Suggested Development Phases

## Phase 1 — Foundation

Build:

- Authentication.
- Application shell.
- Navigation.
- Accounts.
- Strategy management.
- Basic trade CRUD.
- Trade journal.
- Screenshot uploads.

## Phase 2 — Performance Engine

Build:

- P&L calculations.
- R calculations.
- Win rate.
- Profit factor.
- Expectancy.
- Equity curve.
- Growth.
- Drawdown.
- Dashboard.

## Phase 3 — Trading Calendar

Build:

- Monthly calendar.
- Daily aggregation.
- Day drill-down.
- Monthly summary.

## Phase 4 — Advanced Journaling

Build:

- Mistakes.
- Psychology.
- Rule violations.
- Setup grading.
- Checklist snapshots.
- Planned vs actual execution.

## Phase 5 — Analytics

Build:

- Strategies.
- Pairs.
- Sessions.
- Weekdays.
- Setup grades.
- Mistakes.
- Psychology.
- Risk.

## Phase 6 — Playbook

Build:

- Strategy detail pages.
- Setups.
- Rules.
- Checklists.
- Example screenshots.
- Historical performance.

## Phase 7 — Historical Setup Analysis

Build:

- Setup quality score.
- Historical matching.
- Confidence/sample labels.
- Analytics comparison tool.

## Phase 8 — Reports and Review

Build:

- Weekly review.
- Monthly review.
- Reports.
- CSV/Excel export.
- Goals.
- Milestones.

---

# 117. MVP Acceptance Criteria

The MVP is considered complete when the user can:

1. Login.
2. Create a trading account.
3. Set the account starting balance.
4. Create trading strategies.
5. Add strategy setups.
6. Add a completed trade.
7. Select pair and strategy.
8. Record risk and result.
9. Upload before/after screenshots.
10. Record mistakes and lessons.
11. Record psychology.
12. View trade journal history.
13. View win rate.
14. View net P&L.
15. View net R.
16. View equity curve.
17. View account growth percentage.
18. View current/max drawdown.
19. View trades on a monthly calendar.
20. Click a calendar day and inspect trades.
21. Compare strategies.
22. Identify best strategy.
23. Identify most expensive mistake.
24. View pair/session analytics.
25. Filter analytics by date/account/strategy.
26. View strategy historical sample size.
27. View milestone progress.

---

# 118. Success Criteria

The system is successful if the trader can reliably answer:

- What is my real win rate?
- What is my expectancy?
- What is my profit factor?
- What is my average R?
- Which strategy has the strongest edge?
- Which strategy is losing money?
- Which pair should I focus on?
- Which session performs best?
- Which day performs best?
- Which setup grade performs best?
- What mistake costs me the most?
- Do I perform better when I follow my rules?
- How much R have mistakes cost me?
- How much has the account grown?
- What is my current and maximum drawdown?
- Am I improving month by month?
- Which historical conditions produce my best results?

---

# 119. Product Principle

The application should not encourage the trader to focus only on win rate.

A professional performance model should emphasize:

```text
Expectancy
+
R-Multiple
+
Profit Factor
+
Drawdown
+
Risk Consistency
+
Execution Quality
+
Rule Compliance
+
Sample Size
+
Strategy Performance
```

The ultimate purpose of the system is:

> Convert subjective trading decisions into measurable data, identify repeatable strengths, eliminate costly mistakes, and track long-term trading growth through disciplined process improvement.

---

# 120. Final Recommended Technology Stack

## Frontend

```text
Next.js
React
TypeScript
shadcn/ui
Tailwind CSS
TanStack Query
React Hook Form
Zod
Recharts
Lucide React
date-fns
```

## Backend

```text
Laravel 13
PHP 8.4+
Laravel Sanctum
Laravel Storage
Laravel Queue
Laravel Scheduler
Laravel Policies
Laravel API Resources
Redis
```

## Database

```text
MySQL 8+
```

or:

```text
PostgreSQL 16+
```

## Production Infrastructure

```text
Nginx
PHP-FPM
Redis
Queue Worker
Cron / Laravel Scheduler
Cloudflare
S3-compatible object storage
Automated database backups
```

---

# 121. Recommended Product Name Ideas

Optional working names:

- TradeProcess.
- EdgeTracker.
- TradeGrowth.
- Trading OS.
- EdgeJournal.
- TradeMetrics.
- ProcessTrader.
- AlphaJournal.
- EdgeBook.
- TradeCore.

A good internal project name could be:

```text
TradeGrowth
```

because the platform is focused on both process quality and account growth.

---

**End of Software Requirements Specification**


# 122. Frontend Sidebar Design Specification

The application shall use a professional trading-platform sidebar optimized for fast navigation, clear information hierarchy, and dense analytics workflows.

The sidebar must feel similar to a premium finance, trading, or analytics dashboard rather than a generic admin template.

---

# 123. Sidebar Design Goals

The sidebar must:

- Be visually clean and compact.
- Keep the most-used trading actions accessible at all times.
- Clearly separate Trading, Analytics, Performance, and Playbook features.
- Support expanded and collapsed desktop modes.
- Support a mobile slide-over drawer.
- Show the currently selected trading account.
- Provide a prominent Add Trade action.
- Highlight the active route clearly.
- Support dark and light themes.
- Avoid excessive borders and visual clutter.
- Use consistent spacing and icon sizing.
- Remain usable with many navigation items.

---

# 124. Recommended Sidebar Structure

```text
┌──────────────────────────────┐
│ TradeGrowth                  │
│ Trading Performance OS       │
├──────────────────────────────┤
│ Account                      │
│ Funded Account #01        ▼  │
├──────────────────────────────┤
│ + Add Trade                  │
├──────────────────────────────┤
│ Dashboard                    │
│                              │
│ TRADING                      │
│ Trade Journal                │
│ Trade Calendar               │
│ Screenshots                  │
│                              │
│ ANALYTICS                    │
│ Overview                     │
│ Strategies                   │
│ Pairs                        │
│ Sessions                     │
│ Weekdays                     │
│ Setup Grades                 │
│ Mistakes                     │
│ Psychology                   │
│ Risk Analysis                │
│                              │
│ PERFORMANCE                  │
│ Account Growth               │
│ Equity Curve                 │
│ Drawdown                     │
│ Daily Performance            │
│ Monthly Performance          │
│ Goals                        │
│                              │
│ PLAYBOOK                     │
│ Strategies                   │
│ Setups                       │
│ Trading Rules                │
│ Checklists                   │
│                              │
│ Reports                      │
│ Accounts                     │
│ Settings                     │
├──────────────────────────────┤
│ User Profile                 │
│ Janidu                       │
│ Logout                       │
└──────────────────────────────┘
```

---

# 125. Sidebar Navigation Order

Recommended final order:

```text
Dashboard

Primary Action
└── Add Trade

Trading
├── Trade Journal
├── Trade Calendar
└── Screenshots

Analytics
├── Overview
├── Strategies
├── Pairs
├── Sessions
├── Weekdays
├── Setup Grades
├── Mistakes
├── Psychology
└── Risk Analysis

Performance
├── Account Growth
├── Equity Curve
├── Drawdown
├── Daily Performance
├── Monthly Performance
└── Goals

Playbook
├── Strategies
├── Setups
├── Trading Rules
└── Checklists

Reports

Accounts

Settings
```

The Add Trade action should be visually stronger than normal navigation links.

---

# 126. Sidebar Width

Recommended dimensions:

Expanded desktop:

```text
Width: 260px to 280px
```

Recommended default:

```text
272px
```

Collapsed desktop:

```text
Width: 68px to 76px
```

Recommended:

```text
72px
```

Mobile:

```text
Width: min(88vw, 320px)
```

The main content area must automatically adjust when the sidebar collapses.

---

# 127. Sidebar Header

The sidebar header shall contain:

- Application logo or mark.
- Product name.
- Optional product subtitle.
- Collapse button on desktop.

Expanded example:

```text
[Logo] TradeGrowth
       Trading Performance OS
```

Collapsed mode:

```text
[Logo]
```

Recommended UI behavior:

- Clicking logo navigates to Dashboard.
- Collapse control is visible on hover or permanently at the top-right.
- Tooltip appears for logo and controls in collapsed mode.

---

# 128. Trading Account Selector

The selected trading account must appear near the top of the sidebar.

Example:

```text
Account

Funded Account #01
$5,325   +6.5%
```

On click, open a shadcn DropdownMenu or Popover.

Dropdown content:

```text
Select Account

✓ Funded Account #01
  $5,325

  Personal Live
  $1,250

  Demo Account
  $10,000

──────────────

Manage Accounts
+ Add Account
```

The account selector should display:

- Account name.
- Optional account type.
- Current balance.
- Growth percentage.

In collapsed mode:

- Show a compact account icon.
- Tooltip shows selected account name.
- Clicking opens the same selector.

---

# 129. Add Trade Primary Action

The sidebar must provide a highly visible Add Trade button.

Expanded mode:

```text
+ Add Trade
```

Collapsed mode:

```text
+
```

Behavior:

- Opens `/trades/new`
- May optionally support keyboard shortcut.
- Recommended shortcut:

```text
N
```

or:

```text
Ctrl/Cmd + N
```

The shortcut should not trigger while typing inside form fields.

Recommended component:

```text
Button
```

from shadcn/ui.

The Add Trade button should use the primary brand token.

---

# 130. Navigation Group Labels

Navigation sections should use small uppercase or muted labels.

Example:

```text
TRADING
ANALYTICS
PERFORMANCE
PLAYBOOK
```

Recommended style:

```text
text-xs
font-medium
tracking-wide
text-muted-foreground
```

Group labels disappear in collapsed mode.

Group spacing must be large enough to visually distinguish sections without using heavy separators.

---

# 131. Navigation Item Design

Each navigation item shall include:

- Icon.
- Label.
- Optional badge/count.
- Active state.
- Hover state.
- Keyboard focus state.

Expanded item:

```text
[Icon] Trade Journal
```

Collapsed item:

```text
[Icon]
```

Collapsed items must show tooltips.

Recommended dimensions:

```text
Height: 40px
Horizontal padding: 12px
Gap: 10px
Border radius: 8px to 10px
Icon size: 18px
```

---

# 132. Recommended Lucide Icons

Use `lucide-react`.

Suggested mapping:

```text
Dashboard            LayoutDashboard
Add Trade            Plus
Trade Journal        NotebookTabs
Trade Calendar       CalendarDays
Screenshots          Images

Analytics Overview   ChartNoAxesCombined
Strategies           Target
Pairs                CandlestickChart
Sessions             Clock3
Weekdays             CalendarRange
Setup Grades         BadgeCheck
Mistakes             TriangleAlert
Psychology           Brain
Risk Analysis        ShieldAlert

Account Growth       TrendingUp
Equity Curve         ChartSpline
Drawdown             TrendingDown
Daily Performance    CalendarClock
Monthly Performance  CalendarRange
Goals                Goal

Playbook Strategies  BookOpenCheck
Setups                Layers3
Trading Rules        ListChecks
Checklists           ClipboardCheck

Reports              FileChartColumn
Accounts             WalletCards
Settings             Settings

Profile              CircleUserRound
Logout               LogOut
Collapse             PanelLeftClose
Expand               PanelLeftOpen
```

Avoid mixing multiple icon libraries.

---

# 133. Active Navigation State

The active page must be immediately visible.

Recommended active state:

```text
bg-accent
text-accent-foreground
font-medium
```

Optional subtle left indicator:

```text
2px to 3px active bar
```

Do not overuse strong background colors.

For nested routes:

```text
/analytics/strategies/123
```

the parent item:

```text
Strategies
```

must remain active.

---

# 134. Hover State

Hover should be subtle.

Recommended:

```text
hover:bg-muted
hover:text-foreground
```

No aggressive animations.

Transition:

```text
transition-colors duration-150
```

---

# 135. Sidebar Badges

Optional badges can be shown for meaningful counts.

Examples:

```text
Trade Journal       8
Goals               2
```

Possible uses:

- Open trades.
- Incomplete journals.
- Active goals.
- Unreviewed trades.

Use small badges only where useful.

Do not show badges on every item.

---

# 136. Sidebar Footer

The bottom section shall contain:

- User profile.
- User name.
- Optional email.
- Theme switch.
- Settings shortcut.
- Logout.

Expanded example:

```text
[Avatar] Janidu
         Trader

        [⋮]
```

Clicking should open:

```text
Profile
Theme
Settings
Logout
```

Recommended shadcn components:

- Avatar.
- DropdownMenu.
- Separator.

---

# 137. Sticky Behavior

On desktop:

```text
position: fixed
height: 100vh
```

or use a sticky application shell.

The sidebar itself must not scroll away.

If navigation exceeds viewport height:

- Header remains fixed.
- Account selector remains near top.
- Navigation section becomes independently scrollable.
- Footer remains fixed at bottom.

Recommended conceptual layout:

```text
Sidebar
├── Header
├── Account Selector
├── Add Trade
├── ScrollArea
│   └── Navigation
└── Footer
```

Use shadcn `ScrollArea`.

---

# 138. Collapsible Desktop Sidebar

The desktop sidebar must support:

```text
Expanded
Collapsed
```

The user's preference should persist.

Recommended storage:

```text
localStorage
```

or user preference in backend settings.

Collapsed behavior:

- Hide text labels.
- Hide group labels.
- Center icons.
- Keep active states.
- Show tooltips.
- Keep Add Trade as icon button.
- Keep account selector as icon.

The collapse transition must not cause large layout shifts.

---

# 139. Mobile Sidebar

For screens below the desktop breakpoint:

- Sidebar becomes hidden by default.
- Top header shows menu button.
- Clicking opens a slide-over drawer.

Recommended component:

```text
Sheet
```

from shadcn/ui.

Structure:

```text
[Menu] TradeGrowth              [Account]
──────────────────────────────────────────
Main Page Content
```

Mobile drawer:

```text
SheetContent side="left"
```

Recommended width:

```text
w-[88vw] max-w-[320px]
```

The drawer should close automatically after route navigation.

---

# 140. Mobile Top Header

Mobile pages should include a compact sticky top header.

Suggested:

```text
[☰] TradeGrowth       [Account] [Profile]
```

The page title may appear below or inside the page content header.

Do not duplicate the entire desktop sidebar header on mobile.

---

# 141. Tablet Behavior

At medium widths:

Option A:

- Collapsed sidebar by default.

Option B:

- Full sidebar until space becomes limited.

Recommended:

```text
Mobile: < 768px
Tablet compact: 768px–1199px
Desktop expanded: >= 1200px
```

On tablet, collapsed 72px mode provides more room for charts.

---

# 142. Sidebar Theme Design

Dark mode should be the primary trading experience.

Suggested semantic tokens:

```text
--sidebar-background
--sidebar-foreground
--sidebar-muted
--sidebar-accent
--sidebar-accent-foreground
--sidebar-border
--sidebar-primary
--sidebar-primary-foreground
```

Do not use raw hex values inside navigation components.

The exact palette should be controlled through CSS variables.

---

# 143. Sidebar Visual Style

The sidebar should follow these principles:

- Minimal border usage.
- Slight contrast from main content.
- No gradients required.
- No excessive shadows.
- Rounded navigation items.
- Strong spacing rhythm.
- Small, clean icons.
- Calm financial dashboard aesthetic.

Avoid:

- Oversized icons.
- Bright neon colors across the entire sidebar.
- Multiple competing accent colors.
- Heavy card borders around every navigation item.
- Excessive animation.

---

# 144. Recommended Application Shell

Desktop:

```text
┌─────────────┬──────────────────────────────────┐
│             │ Top Page Header                  │
│             ├──────────────────────────────────┤
│  Sidebar    │                                  │
│             │                                  │
│             │       Main Page Content          │
│             │                                  │
│             │                                  │
└─────────────┴──────────────────────────────────┘
```

Recommended main content:

```text
margin-left: sidebar width
min-height: 100vh
```

When collapsed:

```text
margin-left: collapsed sidebar width
```

Use CSS layout rather than manually applying margins across each page.

---

# 145. Frontend Layout Components

Recommended component structure:

```text
components/layout/
├── app-shell.tsx
├── app-sidebar.tsx
├── sidebar-header.tsx
├── sidebar-account-selector.tsx
├── sidebar-nav.tsx
├── sidebar-nav-group.tsx
├── sidebar-nav-item.tsx
├── sidebar-footer.tsx
├── mobile-header.tsx
├── mobile-sidebar.tsx
└── page-header.tsx
```

---

# 146. Navigation Configuration

Navigation items should be configuration-driven rather than hardcoded repeatedly.

Example TypeScript concept:

```text
type SidebarNavItem = {
  title: string
  href: string
  icon: LucideIcon
  badge?: number | string
  match?: string[]
}

type SidebarNavGroup = {
  label?: string
  items: SidebarNavItem[]
}
```

Recommended config location:

```text
src/config/navigation.ts
```

This makes ordering and permission-based rendering easier.

---

# 147. Example Navigation Configuration

```text
Dashboard

Trading
  Trade Journal
  Trade Calendar
  Screenshots

Analytics
  Overview
  Strategies
  Pairs
  Sessions
  Weekdays
  Setup Grades
  Mistakes
  Psychology
  Risk Analysis

Performance
  Account Growth
  Equity Curve
  Drawdown
  Daily Performance
  Monthly Performance
  Goals

Playbook
  Strategies
  Setups
  Trading Rules
  Checklists

Reports
Accounts
Settings
```

The frontend should map this configuration into sidebar groups.

---

# 148. Sidebar Route Map

Recommended routes:

```text
/dashboard

/trades/new
/trades
/trades/[id]
/calendar
/screenshots

/analytics
/analytics/strategies
/analytics/pairs
/analytics/sessions
/analytics/weekdays
/analytics/setup-grades
/analytics/mistakes
/analytics/psychology
/analytics/risk

/performance/growth
/performance/equity
/performance/drawdown
/performance/daily
/performance/monthly
/performance/goals

/playbook/strategies
/playbook/strategies/[id]
/playbook/setups
/playbook/rules
/playbook/checklists

/reports
/accounts
/settings
```

---

# 149. Sidebar Permissions

If multiple user roles are added later, sidebar items should respect authorization.

Example:

```text
Trader:
all personal trading features

Administrator:
system administration features
```

Do not rely on sidebar hiding for security.

Laravel backend authorization remains mandatory.

---

# 150. Account Status in Sidebar

The selected account can optionally show a small status indicator.

Examples:

```text
LIVE
FUNDED
DEMO
EVALUATION
```

Use compact semantic badges.

Example:

```text
Funded Account #01
FUNDED
$5,325  +6.5%
```

Avoid making status badges visually dominant.

---

# 151. Drawdown Warning in Sidebar

For funded accounts, optionally show a compact risk warning below the account selector.

Example:

```text
Daily DD Used
███░░░░░░░  32%
```

If nearing a limit:

```text
Daily DD Used
████████░░  82%
```

This widget should remain compact.

It should link to:

```text
/analytics/risk
```

Recommended only for funded/evaluation accounts.

---

# 152. Sidebar Growth Snapshot

An optional compact performance card may be placed beneath the account selector.

Example:

```text
This Month
+5.2%

Net R
+14.6R
```

Do not show this card if it makes the sidebar crowded.

The preferred order remains:

```text
Account Selector
Add Trade
Navigation
```

---

# 153. Sidebar Keyboard Navigation

The sidebar must support:

- Tab navigation.
- Enter activation.
- Space activation where appropriate.
- Escape to close mobile drawer.
- Visible focus ring.

Collapsed tooltips must remain keyboard-accessible.

---

# 154. Accessibility Requirements for Sidebar

Required:

- `nav` semantic element.
- `aria-current="page"` for active links.
- Accessible labels for icon-only buttons.
- Tooltips for collapsed items.
- Correct focus management for mobile Sheet.
- Account dropdown must be keyboard navigable.
- Collapse button must have accessible name.

---

# 155. Sidebar Loading State

When user/account information is loading:

Display:

- Logo immediately.
- Skeleton for account selector.
- Navigation may render immediately.
- Skeleton for user footer.

Do not block the entire sidebar while account data loads.

---

# 156. Sidebar Error State

If account data fails:

Display:

```text
Account unavailable
Retry
```

Navigation should still remain usable where possible.

---

# 157. Sidebar Empty Account State

If user has no account:

Replace account selector content with:

```text
No Trading Account

+ Create Account
```

Add Trade should either:

- Redirect to account creation first.
- Or be disabled with explanation.

Preferred behavior:

Click Add Trade → if no account exists → open Create Account flow.

---

# 158. Recommended shadcn Components

Use:

```text
Button
Tooltip
DropdownMenu
Avatar
Badge
Separator
ScrollArea
Sheet
Popover
Collapsible
Command
Progress
```

Possible use:

- `Command` inside account selector when many accounts exist.
- `Progress` for funded drawdown buffer.
- `Collapsible` only for optional nested sidebar groups.

---

# 159. Nested Navigation Behavior

Avoid deep navigation nesting.

Maximum recommended:

```text
2 levels
```

Example:

```text
Analytics
└── Strategies
```

Do not create:

```text
Analytics
└── Strategies
    └── Advanced
        └── Monthly
```

Detailed pages should use page-level tabs instead.

---

# 160. Page-Level Tabs vs Sidebar

Sidebar should contain major product areas only.

For example, Strategy Detail:

```text
Overview
Trades
Setups
Checklist
Rules
Screenshots
Analytics
```

These should appear as tabs within:

```text
/playbook/strategies/[id]
```

not as new sidebar items.

This prevents sidebar bloat.

---

# 161. Sidebar Interaction Performance

Sidebar actions should feel instant.

Requirements:

- Route prefetch where appropriate.
- No API request required just to expand/collapse.
- Persist collapse state locally.
- Avoid re-rendering entire sidebar for small KPI updates.
- Memoize navigation config if needed.

---

# 162. Sidebar Acceptance Criteria

The frontend sidebar is complete when:

1. Desktop expanded mode works.
2. Desktop collapsed mode works.
3. Collapse state persists.
4. Mobile drawer works.
5. Account selector works.
6. Add Trade button works.
7. All major routes are accessible.
8. Active route is highlighted.
9. Tooltips appear in collapsed mode.
10. User footer works.
11. Dark and light themes work.
12. Navigation is keyboard accessible.
13. Sidebar navigation can scroll independently.
14. Header/footer remain accessible.
15. Account switching updates dashboard context.
16. Funded-account warning can be displayed.
17. Route changes close the mobile drawer.
18. Sidebar remains visually clean at all supported viewport sizes.

---

# 163. Final Sidebar UX Principle

The sidebar should allow the trader to move from:

```text
Record Trade
→ Review Journal
→ Inspect Calendar
→ Analyze Strategy
→ Measure Growth
→ Review Mistakes
→ Improve Playbook
```

with minimal friction.

The most important actions should always remain one or two clicks away:

```text
Add Trade
Trade Journal
Calendar
Analytics
Account Growth
```

The sidebar should feel like the central navigation system of a professional trading workstation, not a generic CRUD administration panel.

