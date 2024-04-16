<?php

namespace App\Library\QueryBuilder\Concerns;

use App\Library\QueryBuilder\Exceptions\InvalidSortQuery;
use App\Library\QueryBuilder\Filters\AllowedFilter;
use App\Library\QueryBuilder\Sorts\AllowedSort;

trait AddSortToQuery
{
    protected $default_sort;
    protected function checkSorts($sorts)
    {
        return collect($sorts)->map(function ($sort) {
            if ($sort instanceof AllowedFilter) {
                $sort = $sort->getName();
            }
            if ($sort instanceof AllowedSort) {
                return $sort;
            }

            return AllowedSort::field($sort);
        });
    }
    public function allowedSorts($sorts): self
    {
        if ($this->request->sorts()->isEmpty()) {
            return $this;
        }
        $sorts = is_array($sorts) ? $sorts : func_get_args();
        $this->allowedSorts = $this->allowedSorts->concat($this->checkSorts($sorts));

        $this->addFunctionToCallWhenGet('sorts', function () {
            $this->ensureAllSortsExist();
            $this->addSortsToQuery($this->request->sorts());
        });
        return $this;
    }
    public function defaultSort($sorts): self
    {
        if (empty($sorts)) {
            return $this;
        }
        return $this->defaultSorts(func_get_args());
    }
    public function defaultSorts($sorts): self
    {
        $this->addFunctionToCallWhenGet(
            'defaultSorts',
            function () {
                $params = $this->request->aggrid();
                $sortModel = $params['sortModel'];
                if (count($sortModel) > 0) {
                    return $this;
                }
                $rowGroupCols = $params['groups'];
                $groupKeys = $params['groupKeys'];
                if ($this->isDoingGrouping($rowGroupCols, $groupKeys)) {
                    return $this;
                }
                if (!$this->request->sorts()->isEmpty()) {
                    // We've got requested sorts. No need to parse defaults.
                    return $this;
                }

                $sorts = is_array($this->default_sort) ? $this->default_sort : func_get_args();
                $this->checkSorts($sorts)
                    ->each(function (AllowedSort $sort) {
                        $sort->sort($this);
                    });
            }
        );
        $this->default_sort = $sorts;
        return $this;
    }

    protected function addSortsToQuery($sorts)
    {
        $params = $this->request->aggrid();
        $rowGroupCols = $params['groups'];
        $rowGroupCols = $params['groups'];
        $groupKeys = $params['groupKeys'];
        $fields_group = [];
        if ($this->isDoingGrouping($rowGroupCols, $groupKeys)) {
            $field = $rowGroupCols[sizeof($groupKeys)];
            if (!empty($field)) {
                $fields_group = [$field['field']];
            }

        }
        $sorts = collect($sorts);
        if (count($fields_group) > 0) {
            $sorts = $sorts->filter(function (string $property) use ($fields_group) {
                $descending = $property[0] === '-';
                $key = ltrim($property, '-');
                return in_array($key, $fields_group);
            });
        }
        $sorts->each(function (string $property) use ($fields_group) {
            $descending = $property[0] === '-';

            $key = ltrim($property, '-');
            $sort = $this->findSort($key);

            $sort->sort($this, $descending);
        });
    }

    protected function findSort(string $property): ?AllowedSort
    {
        return $this->allowedSorts
            ->first(function (AllowedSort $sort) use ($property) {
                return $sort->isSort($property);
            });
    }

    protected function ensureAllSortsExist(): void
    {
        $requestedSortNames = $this->request->sorts()->map(function (string $sort) {
            return ltrim($sort, '-');
        });

        $allowedSortNames = $this->allowedSorts->map(function (AllowedSort $sort) {
            return $sort->getName();
        });

        $unknownSorts = $requestedSortNames->diff($allowedSortNames);

        if ($unknownSorts->isNotEmpty()) {
            throw InvalidSortQuery::sortsNotAllowed($unknownSorts, $allowedSortNames);
        }
    }
}
