<?php

namespace App\Library\QueryBuilder\Concerns;

use App\Helpers\AggridHelper;
use DB;

trait AddAgGridToQuery
{
    protected $aggird_filter = [];
    public function allowedAgGrid($aggird_filter = [], $aggird_sort = [])
    {
        $this->is_use_aggird = true;
        $this->aggird_filter = array_merge([
            'date' => function () {
                AggridHelper::convertDateFilterType(...func_get_args());
            },
            'datetime' => function () {
                AggridHelper::convertDateTimeFilterType(...func_get_args());
            },
            'relationship' => function ($filter, $query, $key, $where) {
                $value = $filter['filter'] ?? $filter['values'] ?? null;
                if (!empty($value)) {
                    $query->{$where . 'Has'}($filter['relationship'], function ($query) use ($filter, $value, $where) {
                        $where_type = '';
                        if (is_array($value)) {
                            $where_type = 'In';
                        }

                        $query->{"$where$where_type"}($filter['relationship_field'] . '.id', $value);
                    });
                }
            },
        ], $aggird_filter);
        $this->addFunctionToCallWhenGet(
            'aggrid',
            function () use ($aggird_sort) {
                $params = $this->request->aggrid();
                $sortModel = $params['sortModel'];
                $filterModel = $params['filterModel'];
                $rowGroupCols = $params['groups'];
                $valueCols = $params['valueCols'];
                $groupKeys = $params['groupKeys'];
                if ($this->isDoingGrouping($rowGroupCols, $groupKeys)) {
                    $colsToSelect = [];

                    $rowGroupCol = $rowGroupCols[sizeof($groupKeys)];
                    array_push($colsToSelect, $rowGroupCol['field']);

                    foreach ($valueCols as $key => $value) {
                        array_push($colsToSelect, DB::raw($value['aggFunc'] . '(' . $value['field'] . ') as ' . $value['field']));
                    }
                    $this->select($colsToSelect);
                    $colsToGroupBy = [];

                    $rowGroupCol = $rowGroupCols[sizeof($groupKeys)];
                    array_push($colsToGroupBy, $rowGroupCol['field']);
                    $this->groupBy($colsToGroupBy);
                    $this->addFunctionToCallWhenGet('fields', null);
                }
                if (sizeof($groupKeys) > 0) {
                    $this->where(function ($query) use ($groupKeys, $rowGroupCols) {
                        foreach ($groupKeys as $key => $value) {
                            $colName = $rowGroupCols[$key]['field'];
                            $this->where($colName, $value);
                        }
                    });
                }
                $treeKey = $params['treeKey'];
                $treeValue = $params['treeValue'];
                if (!empty($treeKey)) {
                    if (empty($treeValue)) {
                        $calcFilters = $this->request->calcFilters();
                        if ($calcFilters->isEmpty() && empty($this->request->search)) {
                            $this->whereNull($treeKey);
                        }
                    } else {
                        $this->where($treeKey, $treeValue);
                    }
                }

                if (isset($sortModel) && count($sortModel) > 0) {
                    foreach ($sortModel as $sort) {
                        if (isset($aggird_sort[$sort['colId']])) {
                            $aggird_sort[$sort['colId']]($this, $sort);
                        } else {
                            $this->orderBy($sort['colId'], $sort['sort']);
                        }
                    }
                }
                if (!empty($filterModel)) {
                    foreach ($filterModel as $key => $filter) {
                        if (isset($filter['operator'])) {
                            $filter['operator'] = strtolower($filter['operator']);
                            $this->where(function ($query) use ($filter, $key) {
                                $condition1 = $filter['condition1'];
                                $this->convertFilterType($condition1, $query, $key);
                                $condition2 = $filter['condition2'];
                                $this->convertFilterType($condition2, $query, $key, $filter['operator'] == 'and' ? 'where' : 'orWhere');
                            });
                        } else {
                            $this->convertFilterType($filter, $this, $key);
                        }
                    }
                }
                return $this;
            }
        );
        return $this;
    }
    public function convertFilterType($filter, $query, $key, $where = 'where')
    {
        $handle = $this->aggird_filter[$filter['filterType']] ?? null;
        if (empty($handle)) {
            AggridHelper::convertFilterType($filter, $query, $key, $where);
        } else {
            $handle($filter, $query, $key, $where);
        }
    }
    protected function isDoingGrouping($rowGroupCols, $groupKeys)
    {
        // we are not doing grouping if at the lowest level. we are at the lowest level
        // if we are grouping by more columns than we have keys for (that means the user
        // has not expanded a lowest level group, OR we are not grouping at all).
        return count($rowGroupCols) > count($groupKeys);
    }
}
