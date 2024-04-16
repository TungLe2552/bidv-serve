<?php

namespace App\Library\QueryBuilder\Filters;

use Illuminate\Database\Eloquent\Builder;

interface Filter
{
    public function __invoke(Builder $query, FilterParams $value, string $property);
}
