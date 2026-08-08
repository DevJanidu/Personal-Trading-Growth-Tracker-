<?php

namespace App\Policies;

use App\Models\MarketCondition;
use App\Policies\Concerns\OwnedDirectlyByUser;

class MarketConditionPolicy
{
    /** @use OwnedDirectlyByUser<MarketCondition> */
    use OwnedDirectlyByUser;
}
