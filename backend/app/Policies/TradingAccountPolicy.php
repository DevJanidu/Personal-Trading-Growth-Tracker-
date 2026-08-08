<?php

namespace App\Policies;

use App\Models\TradingAccount;
use App\Policies\Concerns\OwnedDirectlyByUser;

class TradingAccountPolicy
{
    /** @use OwnedDirectlyByUser<TradingAccount> */
    use OwnedDirectlyByUser;
}
