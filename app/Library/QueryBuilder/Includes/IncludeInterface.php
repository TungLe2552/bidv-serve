<?php

namespace App\Library\QueryBuilder\Includes;

use Illuminate\Database\Eloquent\Builder;

interface IncludeInterface
{
    public function __invoke(Builder $query, string $include);
}
