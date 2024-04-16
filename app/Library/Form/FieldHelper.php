<?php

namespace App\Library\Form;

use App\Helpers\GeometryCreator;
use App\Helpers\System\CacheHelper;
use Illuminate\Support\Arr;

class FieldHelper
{
    public static function getFieldByType(string $type, $field, $config = []): IField
    {
        switch ($type) {
            case 'number':
                return new NumberField($field, $config);
            case 'double':
                return new DoubleField($field, $config);
            case 'date':
                return new DateField($field, $config);
            default:
                return new TextField($field, $config);
        }
    }
}
interface IField
{
    public function getFormat();
    public function isMulti();
    public function getDefaultValue();
    public function getFieldExport();
}
abstract class AField implements IField
{
    public function isComplex()
    {
        return false;
    }

    abstract protected function getCode();
    public $info;
    public $format;
    public function getFieldExport()
    {
        return $this->getCode();
    }
}
class TextField extends AField
{
    protected function getCode()
    {
        return $this->info['value'] ?? $this->info['code'] ?? $this->info['id'];
    }
    public function __construct($field)
    {
        $this->info = $field;
        $this->format = new STATIC_FORMAT($this->getCode());
    }
    public function isMulti()
    {
        return false;
    }
    public function getDefaultValue()
    {
        return null;
    }
    public function getFormat()
    {
        return $this->format;
    }
}
class DateField extends TextField
{
    public function getDefaultValue()
    {
        return null;
    }
}
class NumberField extends TextField
{
    public function getDefaultValue()
    {
        return 0;
    }
}
class DoubleField extends NumberField
{
}
class STATIC_OBJECT_FORMAT
{
    public $keys;

    public function __construct($keys = [])
    {
        $this->keys = $keys;
    }
    function getKey()
    {
        return $this->keys;
    }
    function getValue($model)
    {
        $keys = $this->keys;
        return array_reduce($keys, function ($acc, $key) use ($model) {
            $acc[$key] = Arr::get($model, $key);
            return $acc;
        }, []);
    }
    function setValue($model, $value)
    {
        $keys = $this->keys;
        return array_reduce($keys, function ($acc, $key) use ($model, $value) {
            Arr::set($model, $key, Arr::get($value, $key));
            return $acc;
        }, []);
    }
    function mapping($model, $new_keys, $value)
    {
        foreach ($this->keys as $key) {
            Arr::set($model, $key, Arr::get($value, $new_keys[$key] ?? $key));
        }
        return $model;
    }
};

class STATIC_FORMAT
{
    public $key;
    public $_format;
    public function __construct($key, $format = null)
    {
        $this->key = $key;
        $this->_format = ['setValue' => $format ?? function ($value) {
            return $value;
        }];
    }
    function getKey()
    {
        return $this->key;
    }
    function getValue($model)
    {
        return $this->_format['setValue'](Arr::get($model, $this->key));
    }
    function setValue($model, $value)
    {
        Arr::set($model, $this->key, $value);
        $model[$this->key] = $value;
        return $model;
    }
    function mapping($model, $new_key, $value)
    {
        $new_key = empty($new_key) ? $this->key : $new_key;
        Arr::set($model, $new_key, $value);
        return $model;
    }
};
