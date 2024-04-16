<?php

namespace App\Helpers\Dynamic;

use App\Models\Base\IrModel;
use App\Models\Base\IrModelField;
use Arr;

final class DynamicTableHelper
{
    public static function getFieldName(IrModel $table, IrModelField $field)
    {
        return $field->name;
    }
    public static function getFieldValue(IrModelField $field, $value)
    {
        $value = Arr::get($value, $field['code']);
        if ($field->type == 'boolean') {
            return $value ? 'True' : 'False';
        }
        return $value;
    }
}
