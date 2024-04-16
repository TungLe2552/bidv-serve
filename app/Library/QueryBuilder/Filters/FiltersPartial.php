<?php

namespace App\Library\QueryBuilder\Filters;

use Illuminate\Support\Str;

class FiltersPartial implements Filter
{
    const DEFAULT_NEGATIVE_CHAR = '!';


    public function __invoke($query, FilterParams $params, string $property)
    {
        $value = $params->getValue();
        if ($this->sanitise($value) === 'null') {
            if ($this->isNegative($value)) {
                $query->whereNotNull($property);
            } else {
                $query->whereNull($property);
            }
        } else
        if (is_string($value))
            if ($this->isNegative($value)) {
                $query->where($property, '!=', $this->sanitise($params->getValue()));
            } else {
                $query->where($property, $params->getValue());
            }
    }

    protected function isNegative($value): bool
    {
        return Str::startsWith($value, self::DEFAULT_NEGATIVE_CHAR);
    }
    protected function sanitise(string $value): string
    {
        return Str::after($value, self::DEFAULT_NEGATIVE_CHAR);
    }
}
