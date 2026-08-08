<?php

namespace Tests\Unit\Enums;

use App\Enums\AccountTransactionType;
use PHPUnit\Framework\TestCase;

class AccountTransactionTypeTest extends TestCase
{
    public function test_deposit_and_refund_are_positive(): void
    {
        $this->assertSame(1, AccountTransactionType::Deposit->defaultSign());
        $this->assertSame(1, AccountTransactionType::Refund->defaultSign());
    }

    public function test_withdrawal_fee_and_profit_split_are_negative(): void
    {
        $this->assertSame(-1, AccountTransactionType::Withdrawal->defaultSign());
        $this->assertSame(-1, AccountTransactionType::Fee->defaultSign());
        $this->assertSame(-1, AccountTransactionType::ProfitSplit->defaultSign());
    }

    public function test_adjustment_carries_its_own_sign(): void
    {
        $this->assertNull(AccountTransactionType::Adjustment->defaultSign());
    }
}
