<?php

namespace App\DataObjects;

class DataObject
{
    public static function fromArray(array $data): self
    {
        $reflectionClass = new \ReflectionClass(static::class);
        $parameters = [];

        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();
            if (isset($data[$propertyName])) {
                $parameters[] = $data[$propertyName];
            } else {
                throw new \InvalidArgumentException("Missing property: $propertyName");
            }
        }

        return $reflectionClass->newInstanceArgs($parameters);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
