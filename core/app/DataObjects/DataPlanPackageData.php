<?php

namespace App\DataObjects;

class DataPlanPackageData extends DataObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $service,
        public readonly string $provider,
        public readonly string $description,
        public readonly string $validity,
        public readonly string $currency,
        public readonly int $main_price,
        public readonly int $price,
    )
    {
    }
}
