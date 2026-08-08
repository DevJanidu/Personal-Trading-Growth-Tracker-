<?php

namespace App\Enums;

enum DrawdownCalculationType: string
{
    case BalanceBased = 'balance_based';
    case EquityBased = 'equity_based';
    case Trailing = 'trailing';
}
