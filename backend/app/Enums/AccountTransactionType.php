<?php

namespace App\Enums;

enum AccountTransactionType: string
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case Fee = 'fee';
    case Refund = 'refund';
    case ProfitSplit = 'profit_split';
    case Adjustment = 'adjustment';

    /**
     * The signed direction this transaction type applies to the account balance
     * (DATABASE_SCHEMA.md §3.2: "deposit/refund add, withdrawal/fee/profit_split
     * subtract, adjustment can be ±"). Only `adjustment` carries its own sign on
     * the stored amount, so it returns null here.
     */
    public function defaultSign(): ?int
    {
        return match ($this) {
            self::Deposit, self::Refund => 1,
            self::Withdrawal, self::Fee, self::ProfitSplit => -1,
            self::Adjustment => null,
        };
    }
}
