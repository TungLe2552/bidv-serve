<?php

namespace App\Library\QueryBuilder\Filters\Custom;

use App\Library\QueryBuilder\Filters\Filter;
use App\Library\QueryBuilder\Filters\FilterParams;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class FilterInJsonArray implements Filter
{
    public function __invoke(Builder $query, FilterParams $params, string $property)
    {
        $query->whereRaw(DB::raw($property . "::jsonb @> '[" . $params->getValue() . "]'"));
    }
}
