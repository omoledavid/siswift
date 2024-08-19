<?php

namespace App\Enums;

enum ProductStatus: int
{

    case PENDING = 0;
    case ACTIVE = 1;
    case DELIST = 2;
    case RELIST = 3;
}
