<?php

namespace App\Library\QueryBuilder\Sorts\Custom;

use App\Library\QueryBuilder\Sorts\Sort;
use Illuminate\Database\Eloquent\Builder;

class IndexSort implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property)
    {
        if ($descending) {
            $query->orderByRaw($property . ' desc,' . $property . '=0');
        } else {
            $query->orderByRaw($property . '=0, ' . $property . ' asc');
        }
    }
}
