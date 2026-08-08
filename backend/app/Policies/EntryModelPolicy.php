<?php

namespace App\Policies;

use App\Models\EntryModel;
use App\Policies\Concerns\OwnedDirectlyByUser;

class EntryModelPolicy
{
    /** @use OwnedDirectlyByUser<EntryModel> */
    use OwnedDirectlyByUser;
}
