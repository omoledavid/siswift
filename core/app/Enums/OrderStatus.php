<?php

namespace App\Enums;

enum OrderStatus: int
{

    case PENDING = 0;
    case PROCESSING = 1;
    case DELIST = 2;
    case RELIST = 3;
}
