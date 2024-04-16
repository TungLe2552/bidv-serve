<?php

namespace App\Library\QueryBuilder\Filters\Custom;

use App\Library\QueryBuilder\Filters\Filter;
use App\Library\QueryBuilder\Filters\FilterParams;
use Illuminate\Database\Eloquent\Builder;

class FilterType implements Filter
{
    protected $operator;
    public function __construct($operator)
    {
        $this->operator = $operator;
    }
    public function __invoke(Builder $query, FilterParams $params, string $property)
    {

        if (!empty($params->getValue()))
            $query->where($property, $this->operator, $params->getValue());
    }
}
