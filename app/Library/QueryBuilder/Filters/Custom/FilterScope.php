<?php

namespace App\Library\QueryBuilder\Filters\Custom;

use App\Library\QueryBuilder\Filters\Filter;
use App\Library\QueryBuilder\Filters\FilterParams;
use Illuminate\Database\Eloquent\Builder;

class FilterScope implements Filter
{
    protected $scope;
    public function __construct(string $scope)
    {
        $this->scope = $scope;
    }
    public function __invoke(Builder $query, FilterParams $params, string $property)
    {
        // local or global scope cua laravel
        $query->{$this->scope}($property, $params->getValue());
    }
}
