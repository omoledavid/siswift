<?php

namespace App\DataObjects;

class AirtimePackageData extends DataObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $service,
        public readonly string $provider,
        public readonly string $description,
        public readonly int $main_discount,
        public readonly int $discount,
    )
    {
    }
}
