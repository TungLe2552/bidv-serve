<?php

namespace App\Traits\Model;

use App\Helpers\System\CacheHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Trait ResponseType.
 */
trait GetAllFieldModel
{

    public function getAllFieldModel(Model $model, $param2 = "", $param3 = false)
    {

        $tableName = $model->getTable();
        $resultArray = CacheHelper::getDataCache('db_table_struct_', $tableName, function () use ($tableName, $param2, $param3) {
            $fields = Schema::getColumnListing($tableName);
            // Các phần tử muốn loại bỏ
            $elementsToRemove = ["created_at", "updated_at"];
            // Loại bỏ các phần tử
            $resultArray = array_diff($fields, $elementsToRemove);
            if ($param3 && $param2) {
                foreach ($resultArray as $key => $value) {
                    $resultArray[$key] = $param2 . "_" . $value;
                }
            }
            return $resultArray;
        });
        return $resultArray;
    }
}
