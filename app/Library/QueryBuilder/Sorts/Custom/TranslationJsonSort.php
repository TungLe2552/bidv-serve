<?php

namespace App\Library\QueryBuilder\Sorts\Custom;

use App\Library\QueryBuilder\Sorts\Sort;
use Illuminate\Database\Eloquent\Builder;

class TranslationJsonSort implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property)
    {
        $query->orderBy($property . '->' . app()->getLocale(), $descending ? 'desc' : 'asc');
    }
}
