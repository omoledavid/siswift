<?php

namespace App\Enums;

enum EscrowStatus: string
{
    /**
     * Escrow started, and waiting for sellers confirmation
     */
    case Initiated = 'initiated';

    /**
     * Sellers reject the escrow for refund
     */
    case Rejected = 'rejected';

    /**
     * Sellers confirmed the escrow for delivery
     */
    case Confirmed = 'confirmed';

    /**
     * Buyer confirm delivery to credit seller
     */
    case Delivered = 'delivered';
}
