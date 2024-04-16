<?php

namespace App\Models;

use App\Traits\HasSetLayer;
use Illuminate\Database\Eloquent\Model;
use Validator;

class BaseModel extends Model
{
    public static $INCLUDE = [];
    public static $SORT = [];
    public static $SEARCH = [];
    public static $FILTER = [];
    public static $FIELD = [];
    public static $DEFAULT_SORT = [];
    use HasSetLayer;
    public static function getTableName(): string
    {
        $model = new self();
        return $model->getTable();
    }
    public static function validate($type, $info = [])
    {
        $validator = Validator::make($info, [], [], []);
        $validator->validate();
    }

    function scopeDefaultOrder($query)
    {
        $query->orderBy('id');
    }
}
