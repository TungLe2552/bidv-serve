<?php

namespace App\Library\QueryBuilder;

use App\Library\QueryBuilder\Filters\FilterParams;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class QueryBuilderRequest extends Request
{
    protected $aggrid = [];
    public static function fromRequest(Request $request): self
    {
        return static::createFrom($request, new self());
    }

    public function includes(): Collection
    {
        $includeParameterName = config('query-builder.parameters.include', 'include');
        $includeParts = $this->get($includeParameterName);


        if (!is_array($includeParts)) {
            $includeParts = explode(config('query-builder.delimiter.include', ','), $this->get($includeParameterName));
        }
        return collect($includeParts)
            ->filter()
            ->map([Str::class, 'camel']);
    }
    public function pagination(): array
    {
        $fieldsPageName = config('query-builder.parameters.pagination.page', 'page');
        $fieldsPerPageName = config('query-builder.parameters.pagination.per_page', 'perpage');
        $fieldsIsPaginateName = config('query-builder.parameters.pagination.is_paginate', 'paginate');
        $fieldsLimitName = config('query-builder.parameters.limit', 'limit');

        $page = $this->get($fieldsPageName);
        $per_page = $this->get($fieldsPerPageName, config('query-builder.pagination.default_size'));
        $is_paginate = $this->boolean($fieldsIsPaginateName) || !empty($page);
        $fields_limit_name = $this->get($fieldsLimitName);

        return ['page' => $page, 'per_page' => $per_page, 'is_paginate' => $is_paginate, 'limit' => $fields_limit_name];
    }

    public function appends(): Collection
    {
        $appendParameterName = config('query-builder.parameters.append', 'append');

        $appendParts = $this->get($appendParameterName);

        if (!is_array($appendParts)) {
            $appendParts = explode(config('query-builder.delimiter.append', ','), strtolower($appendParts));
        }

        return collect($appendParts)->filter();
    }

    public function fields(): Collection
    {
        $fieldsParameterName = config('query-builder.parameters.fields', 'fields');

        $fieldsParts = $this->get($fieldsParameterName);
        if (!is_array($fieldsParts)) {
            $fieldsParts = explode(config('query-builder.delimiter.fields', ','), strtolower($fieldsParts));
        }

        return collect($fieldsParts)->filter();
    }
    public function calcFilters(): Collection
    {
        $fieldsParameterName = config('query-builder.parameters.filter', '$f');

        $fieldsParts = $this->get($fieldsParameterName);
        return collect($fieldsParts)->filter();
    }
    public function calcRelations(): Collection
    {
        $fieldsParameterName = config('query-builder.parameters.relation', '$rl');

        $fieldsParts = $this->get($fieldsParameterName);
        return collect($fieldsParts)->filter();
    }

    public function sorts(): Collection
    {
        $sortParameterName = config('query-builder.parameters.sort', 'sort');

        $sortParts = $this->get($sortParameterName);
        if (empty($sortParts)) {
            $sortParts = [];
        }
        if (is_string($sortParts)) {
            $sortParts = explode(config('query-builder.delimiter.sort', ','), $sortParts);
        }
        return collect($sortParts)->filter();
    }
    public function filters(): Collection
    {
        return collect($this->except(
            [
                config('query-builder.parameters.search', 'search'),
                config('query-builder.parameters.sort', 'sort'),
                config('query-builder.parameters.fields', 'fields'),
                config('query-builder.parameters.append', 'append'),
                config('query-builder.parameters.pagination.page', 'page'),
                config('query-builder.parameters.pagination.per_page', 'perpage'),
                config('query-builder.parameters.pagination.is_paginate', 'paginate'),
                config('query-builder.parameters.include', 'include'),
                config('query-builder.parameters.filter', '$f'),
                config('query-builder.parameters.relation', '$rl'),
                'sortModel',
                'filterModel',
                '$gs',
                '$gks',
                '$gvs',
                '$tree_value',
                '$tree_key'
            ]
        ))->map(function ($item, $key) {
            return new FilterParams($key, $item);
        });
    }

    public function aggrid()
    {
        if (empty($this->aggrid)) {
            $sortModel = $this->get('sortModel');
            if (empty($sortModel)) {
                $sortModel = [];
            }
            $filterModel = $this->get('filterModel');
            if (empty($filterModel)) {
                $filterModel = [];
            }
            $groups = $this->get('$gs');
            if (empty($groups)) {
                $groups = [];
            }
            if (is_string($groups)) {
                $groups = json_decode($groups, true);
            }
            $groupKeys = $this->get('$gks');
            if (empty($groupKeys)) {
                $groupKeys = [];
            }
            if (is_string($groupKeys)) {
                $groupKeys = json_decode($groupKeys, true);
            }
            $valueCols = $this->get('$gvs');
            if (empty($valueCols)) {
                $valueCols = [];
            }
            if (is_string($valueCols)) {
                $valueCols = json_decode($valueCols, true);
            }
            $tree_value = $this->get('$tree_value');
            $tree_key = $this->get('$tree_key');
            $this->aggrid = [
                'sortModel' => $sortModel,
                'filterModel' => $filterModel,
                'groups' => $groups,
                'groupKeys' => $groupKeys,
                'valueCols' => $valueCols,
                'treeKey' => $tree_key,
                'treeValue' => $tree_value,
            ];
        }
        return $this->aggrid;
    }
    public function search(): String
    {
        $searchParamsName = config('query-builder.parameters.search', 'search');
        return $this->get($searchParamsName) ?? '';
    }
}
