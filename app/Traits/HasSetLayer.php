<?php

namespace App\Traits;

use App\Library\QueryBuilder\Filters\AllowedFilter;
use App\Models\Base\IrModel;

trait HasSetLayer
{
    public $is_use_layer = true;
    protected $CUSTOM_OPTION = [];
    function scopeDefaultFilter($query, $property, $is_use)
    {
        if (!boolval($is_use)) {
            return;
        }
        if (!empty($this->CUSTOM_OPTION['DEFAULT_FILTER'])) {
            $fc = $this->CUSTOM_OPTION['DEFAULT_FILTER'];
            $fc($query);
        }
    }
    public function setLayer(IrModel $model)
    {
        if (empty($model)) {
            return;
        }
        $search = [];
        $filter = [];
        $sort = [];
        $default_sorts = [];
        $fields_code = [];
        if ($model->relationLoaded('fields')) {
            $fields = $model['fields'];
            foreach ($fields as $column) {
                $code = $column['code'];
                if ($code == 'code') {
                    $default_sorts = ['code'];
                } else
                if ($code == 'priority') {
                    $default_sorts = ['priority'];
                } else if ($code == 'active') {
                    $default_filter = function ($query) {
                        $query->where('active', true);
                    };
                }
                if ($column['search_by_fulltext_search'] === '1-Always Searchable')
                    $search[] = $code;
                if (!str_contains($code, '.')) {
                    $sort[] = $code;
                    // $fields_code[] = $code;
                }
            }
        }
        if (!empty(static::$DEFAULT_SORT)) {
            $default_sorts=[];
        }
        $filter[] = AllowedFilter::scope('default_filter', 'defaultFilter');
        $this->setOption([
            'search' => $search ?? [],
            'filter' => $filter ?? [],
            'sort' => $sort ?? [],
            'fields' => $fields_code ?? [],
            'default_sorts' => $default_sorts ?? [],
            'default_filter' => $default_filter ?? null,
        ]);
    }
    public function getOption($type)
    {
        return $this->CUSTOM_OPTION[$type] ?? [];
    }
    public function setOption($options)
    {
        $this->CUSTOM_OPTION['SORT'] = array_merge($this->CUSTOM_OPTION['SORT'] ?? [], $options['sort'] ?? []);
        $this->CUSTOM_OPTION['SEARCH'] = array_merge($this->CUSTOM_OPTION['SEARCH'] ?? [], $options['search'] ?? []);
        $this->CUSTOM_OPTION['DEFAULT_SORT'] = array_merge($this->CUSTOM_OPTION['DEFAULT_SORT'] ?? [], $options['default_sorts'] ?? []);
        $this->CUSTOM_OPTION['DEFAULT_FILTER'] = $options['default_filter'] ?? null;
        $this->CUSTOM_OPTION['FILTER'] = array_merge($this->CUSTOM_OPTION['FILTER'] ?? [], $options['filter'] ?? []);
        $this->CUSTOM_OPTION['FIELD'] = array_merge($this->CUSTOM_OPTION['FIELD'] ?? [], $options['fields'] ?? []);
    }
}
