<?php

namespace App\Enums;

enum WebSubSubscriptionStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Failed = 'failed';
}
