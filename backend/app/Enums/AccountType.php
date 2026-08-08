<?php

namespace App\Enums;

enum AccountType: string
{
    case PersonalLive = 'personal_live';
    case Demo = 'demo';
    case Funded = 'funded';
    case PropEvaluation = 'prop_evaluation';
    case PropFunded = 'prop_funded';
    case Backtesting = 'backtesting';
    case Custom = 'custom';

    public function isFundedOrEvaluation(): bool
    {
        return in_array($this, [self::Funded, self::PropEvaluation, self::PropFunded], true);
    }
}
