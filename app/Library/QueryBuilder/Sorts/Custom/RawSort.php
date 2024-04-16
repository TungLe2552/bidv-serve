<?php

namespace App\Library\QueryBuilder\Sorts\Custom;

use App\Library\QueryBuilder\Sorts\Sort;
use Illuminate\Database\Eloquent\Builder;

class RawSort implements Sort
{
    protected $sort;
    public function __construct($sort)
    {
        $this->sort = $sort;
    }
    public function __invoke(Builder $query, bool $descending, string $property)
    {
        $query->orderByRaw($this->sort);
    }
}
