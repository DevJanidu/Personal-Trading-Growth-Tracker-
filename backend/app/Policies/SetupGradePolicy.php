<?php

namespace App\Policies;

use App\Models\SetupGrade;
use App\Policies\Concerns\OwnedDirectlyByUser;

class SetupGradePolicy
{
    /** @use OwnedDirectlyByUser<SetupGrade> */
    use OwnedDirectlyByUser;
}
