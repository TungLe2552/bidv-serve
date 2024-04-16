<?php

namespace App\Library\QueryBuilder\Filters\Custom;

use App\Library\QueryBuilder\Filters\Filter;
use App\Library\QueryBuilder\Filters\FilterParams;
use Illuminate\Database\Eloquent\Builder;

class FilterRelationMulti implements Filter
{
    protected $relation;
    protected $field;
    protected $table;
    public function __construct(string $relation, string $field = 'id', ?string $table = null)
    {
        $this->relation = $relation;
        $this->field = $field;
        $this->table = $table;
    }
    public function __invoke(Builder $query, FilterParams $params, string $property)
    {
        $query->whereHas($this->relation, function ($query) use ($params) {
            $value = is_array($params->getValue()) ? $params->getValue() : explode(",", $params->getValue());
            $query->whereIn(!empty($this->table) ? $this->table . "." . $this->field : $this->field, $value);
        });
    }
}
