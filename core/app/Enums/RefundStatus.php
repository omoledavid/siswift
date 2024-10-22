<?php

namespace App\Enums;

enum RefundStatus: int
{

    case OPEN = 0;
    case CLOSE = 1;
}
