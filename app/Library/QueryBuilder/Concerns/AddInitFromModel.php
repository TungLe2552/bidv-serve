<?php

namespace App\Library\QueryBuilder\Concerns;

use App\Helpers\AggridHelper;
use App\Helpers\Dynamic\TableHelper;

trait AddInitFromModel
{
    public function initFromModelForList($init_table = null, $force_init_table = false): self
    {
        $default_sort = [];
        $option_fields = [];
        $option_search = [];
        $option_sort = [];
        if (method_exists($this->subject, 'getModel')) {
            $model = $this->getModel();
            if (!property_exists($model, 'DYNAMIC_MODEL')) {
                $table = $init_table;
                if (empty($init_table)) {
                    $table = $model->getTable();
                }
                $table = $this->request->get('$table', $table);
                if (!empty($table)) {
                    $model_tmp = TableHelper::getTableByCode($table, false);
                    if (!empty($model_tmp) && method_exists($model, 'setLayer'))
                        $model->setLayer($model_tmp);
                }
            }
            if ($force_init_table) {
                $table = $init_table;
                if (!empty($table)) {
                    $model_tmp = TableHelper::getTableByCode($table, false);
                    if (!empty($model_tmp) && method_exists($model, 'setLayer'))
                        $model->setLayer($model_tmp);
                }
            }
            if ($model->usesTimestamps()) {
                $this->allowedTimestamps();
                $this->defaultSort('-' . $model->getKeyName());
            }
            $this->allowedFilters([$model->getKeyName()]);
            if (property_exists($model, 'is_use_layer') && method_exists($model, 'getOption')) {
                $this->allowedIncludes($model->getOption('INCLUDE'));
                $this->allowedFilters($model->getOption('FILTER'));
                $default_sort = array_merge($default_sort, $model->getOption('DEFAULT_SORT'));
                $option_sort = array_merge($option_sort, $model->getOption('SORT'));
                $option_search = array_merge($option_search, $model->getOption('SEARCH'));
                $option_fields = array_merge($option_fields, $model->getOption('FIELD'));
            }
            if (property_exists($model, 'INCLUDE'))
                $this->allowedIncludes($model::$INCLUDE);
            if (property_exists($model, 'FILTER')) {
                $this->allowedFilters($model::$FILTER);
            }
            if (method_exists($model, 'getFilterConfig')) {
                $this->allowedFilters($model->getFilterConfig());
            }
            if (property_exists($model, 'SORT')) {
                $option_sort = array_merge($option_sort, $model::$SORT);
            }
            if (property_exists($model, 'SEARCH')) {
                $option_search = array_merge($option_search, $model::$SEARCH);
            }
            if (property_exists($model, 'FIELD')) {
                $option_fields = array_merge($option_fields, $model::$FIELD);
            }
            if (property_exists($model, 'DEFAULT_SORT')) {
                $default_sort = array_merge($default_sort, $model::$DEFAULT_SORT);
            }
        }
        if (count($option_sort) > 0) {
            $this->allowedSorts($option_sort);
        }
        if (count($option_search) > 0) {
            $this->allowedSearch($option_search);
        }
        if (count($default_sort) > 0) {
            $this->defaultSorts($default_sort);
        }
        if (count($option_fields) > 0) {
            $this->allowedField($option_fields);
        }
        $this->addInitCalcFilter();
        $this->addInitCalcRelationFilter();
        $this->allowedAgGrid();
        return $this;
    }
    public function initFromModelForShow(): self
    {
        if (method_exists($this->subject, 'getModel')) {
            if (property_exists($this->getModel(), 'INCLUDE'))
                $this->allowedIncludes($this->getModel()::$INCLUDE);
        }
        return $this;
    }
    private function addInitCalcRelationFilter()
    {

        $relations = $this->request->calcRelations();
        if (empty($relations) || $relations->count() < 0) {
            return;
        }
        $relations->each(function ($items, $relation) {
            $this->whereHas($relation, function ($q) use ($items) {
                foreach ($items as $key => $filters) {
                    foreach ($filters as $method => $values) {
                        if (!is_array($values)) {
                            $values = [$values];
                        }
                        foreach ($values as $value) {
                            AggridHelper::convertTextFilterType(
                                [
                                    'type' => $method,
                                    'filter' => $value
                                ],
                                $q,
                                $key,
                                'where'
                            );
                        }
                    }
                }
            });
        });
    }
    private function addInitCalcFilter()
    {
        $filters = $this->request->calcFilters();
        if (empty($filters) || $filters->count() < 0) {
            return;
        }
        $table = $this->request->get('$table');
        $fields = [];
        if (!empty($table)) {
            $table = TableHelper::getTableByCode($table);
            $fields = $table->fields->mapWithKeys(function ($item) {
                return [$item['code'] => $item['type']];
            }, []);
        }
        $this->where(function ($query) use ($filters, $fields) {

            $filters->each(function ($items, $key) use ($fields, $query) {
                foreach ($items as $method => $values) {
                    if (!is_array($values)) {
                        $values = [$values];
                    }
                    foreach ($values as $value) {
                        $type = 'string';
                        if (isset($fields[$key])) {
                            $type = $fields[$key];
                        }
                        if ($type === 'date') {

                            AggridHelper::convertDateFilterType(
                                [
                                    'type' => $method,
                                    'filter' => $value
                                ],
                                $query,
                                $key,
                                'where'
                            );
                        } else {
                            AggridHelper::convertTextFilterType(
                                [
                                    'type' => $method,
                                    'filter' => $value
                                ],
                                $query,
                                $key,
                                'where'
                            );
                        }
                    }
                }
            });
        });
    }
}
