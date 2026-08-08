<?php

namespace App\Policies;

use App\Models\Strategy;
use App\Policies\Concerns\OwnedDirectlyByUser;

class StrategyPolicy
{
    /** @use OwnedDirectlyByUser<Strategy> */
    use OwnedDirectlyByUser;
}
