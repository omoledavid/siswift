<?php

namespace App\Enums;

enum RefundStatus: int
{

    case PENDING = 0;
    case APPROVE = 1;
    case REJECTED = 2;
}
