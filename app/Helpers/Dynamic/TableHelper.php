<?php

namespace App\Helpers\Dynamic;

use App\Helpers\System\CacheHelper;
use App\Models\Base\IrModel;
use App\Models\Base\IrModelFeature;

use App\Constants\ModelForQuery;

final class TableHelper
{
    public static function getTable($table_id, $check = true): IrModel
    {
        $table = CacheHelper::getDataCache('table_struct_', $table_id, function () use ($table_id) {
            return IrModel::with(['fields'])->find($table_id);
        });
        if ($check && empty($table)) {
            abort(404);
        }
        return $table;
    }
    public static function getTableByCode($code, $check = true)
    {
        $table = CacheHelper::getDataCache('table_struct_code_', $code, function () use ($code) {
            return IrModel::with(['fields'])->where('code', $code)->first();
        });
        if ($check && empty($table)) {
            abort(404);
        }
        return $table;
    }
    public static function removeCacheTable($table_id)
    {
        $table = CacheHelper::get('table_struct_', $table_id);
        if (isset($table)) {
            CacheHelper::forget('table_struct_', $table->getKey());
            CacheHelper::forget('table_struct_code_', $table->code);
        }
    }
    public static function getClass(IrModel $table, $table_name = null, $type = '', callable $cb = null)
    {
        if (empty($table_name)) {
            $table_name = $table->getTableDbName();
        }
        $class = null;
        switch ($type) {
            case 'list':
                $class = ModelForQuery::LIST[$table_name] ?? null;
                break;
            case 'form':
                $class = ModelForQuery::FORM[$table_name] ?? null;
                break;
        }
        if (!empty($cb) && !empty($class)) {
            return $cb($class);
        }
        return $class;
    }
    public static function getQuery(IrModel $table, $table_name = null, $type = '')
    {
        $class = self::getClass($table, $table_name, $type);
        if (empty($table_name)) {
            $table_name = $table->getTableDbName();
        }
        if (!empty($class)) {
            $query = $class::query();
        } else {
            $query = IrModelFeature::table($table_name, $table)->query();
        }
        return $query;
    }
}
