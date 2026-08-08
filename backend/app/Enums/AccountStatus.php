<?php

namespace App\Enums;

enum AccountStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
    case Closed = 'closed';
}
