<?php

namespace App\Policies;

use App\Models\TradingSession;
use App\Policies\Concerns\OwnedDirectlyByUser;

class TradingSessionPolicy
{
    /** @use OwnedDirectlyByUser<TradingSession> */
    use OwnedDirectlyByUser;
}
