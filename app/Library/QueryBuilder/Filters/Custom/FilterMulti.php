<?php

namespace App\Library\QueryBuilder\Filters\Custom;

use App\Library\QueryBuilder\Filters\Filter;
use App\Library\QueryBuilder\Filters\FilterParams;
use Illuminate\Database\Eloquent\Builder;

class FilterMulti implements Filter
{
    public function __invoke(Builder $query, FilterParams $params, string $property)
    {
        $value = is_array($params->getValue()) ? $params->getValue() : explode(",", $params->getValue());

        $value = array_map('trim', $value);
        $query->whereIn($property, $value);
    }
}
