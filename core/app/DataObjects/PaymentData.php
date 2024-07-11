<?php

namespace App\DataObjects;

class PaymentData extends DataObject
{
    public function __construct(
        public readonly bool $status,
        public readonly string $reference,
        public readonly string $currency,
        public readonly int $amount,
        public readonly int $fees,
        public readonly string $createDate,
        public readonly string $paidDate,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['status'],
            $data['reference'],
            $data['currency'],
            $data['amount'],
            $data['fees'],
            $data['createdDate'],
            $data['paidDate'],
        );
    }
}
