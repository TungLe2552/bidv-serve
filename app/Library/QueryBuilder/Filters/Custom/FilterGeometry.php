<?php

namespace App\Library\QueryBuilder\Filters\Custom;

use App\Library\QueryBuilder\Filters\Filter;
use App\Library\QueryBuilder\Filters\FilterParams;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class FilterGeometry implements Filter
{
    public function __invoke(Builder $query, FilterParams $params, string $property)
    {
        $geometry = $params->getValue();
        if(empty($geometry)) return;
        if (!is_string($geometry)) {
            $geometry = json_encode($geometry);
        }
        $query->whereRaw('ST_Contains(ST_GeomFromGeoJSON(?)::geometry,geometry::geometry)', [$geometry]);
    }
}
