<?php

namespace App\Library\FormData;

use App\Models\Base\IrModelField;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Library\FormData\Validator\Validator;
use Illuminate\Database\Eloquent\Model;

class Form
{
    public $type;
    public $fields;
    public $table_name;
    public $validator;
    public function __construct($type, $fields, $table_name)
    {
        $this->type = $type;
        $this->table_name = $table_name;
        $this->fields = $fields->reduce(function ($acc, $field) use ($type) {
            if (empty($field['visibility']) || empty($field['visibility'][$type]) || $field['visibility'][$type]) {
                $acc[] = new SingleFormField($field);
            }
            return $acc;
        }, []);
        $this->validator = new Validator($this->table_name);
    }
    public function getRules($data = [],Model $model = null)
    {
        $rules = [];
        foreach ($this->fields as $field) {
            if ($field->required || !empty($data) || !empty($field->getValue($data))) {
                $rules = $field->setRules($rules, $model);
            }
        }
        $rules_validator = $this->validator->getRules($data, $model);
        foreach ($rules_validator as $key => $items) {
            $rules[$key] = array_merge($rules[$key] ?? [], $items);
        }
        return $rules;
    }
    public function getMessages($data = [],Model $model = null)
    {
        $messages = [];
        $messages_validator = $this->validator->getMessages($data, $model);
        foreach ($messages_validator as $key => $items) {
            $messages[$key] = array_merge($messages[$key] ?? [], $items);
        }
        return $messages;
    }
    public function getAttribute()
    {
        $attribute = [];
        foreach ($this->fields as $field) {
            $attribute = $field->setAttribute($attribute);
        }
        return $attribute;
    }

    public function convertData($model)
    {
        $result = [];
        foreach ($this->fields as $field) {
            $result = $field->setValue($result, $model);
        }
        return $result;
    }
}
class FormField
{
    protected $info;
    public $required;
    protected $field;
    protected $convert;
    protected $table;
    public function __construct(IrModelField $field)
    {
        $this->info = $field;
        $this->required = $field['required'];
        $this->setConvert($field);
        $this->table = $field->model->code;
        $this->field = FieldCreator($field->type, [
            'default_value' => $field['default_value'] ?? null,
            'domain' => $field['domain'],
            'field' => $field['meta']['field'] ?? []
        ]);
    }
    protected function setConvert($field)
    {
        $this->convert = new StaticSingleConvert($field['code']);
    }
    public function setRules($rules)
    {
        return $rules;
    }
    public function setAttribute($attribute)
    {
        return $attribute;
    }
    public function getValue($model)
    {
        return $this->convert->getValue($model);
    }
    public function setValue($result, $model)
    {
        $value = $this->convert->getValue($model);
        if (isset($value)) {
            $value = $this->field->convert($value, $model);
        }
        return $this->convert->setValue($result, $value ?? $this->field->getDefaultValue());
    }
}
class SingleFormField extends FormField
{
    public function setRules($rules, $info = null)
    {
        $temp = [];
        if ($this->info['type'] === 'boolean') {
            $temp[] = 'nullable';
        } else {
            $temp[] = ($this->required && !$this->info['read_only']) ? 'required' : 'nullable';
        }
        if (isset($this->info['is_unique']) && $this->info['is_unique']) {
            $temp[] = Rule::unique($this->table)->ignore($info['id'] ?? null);
        }
        $max = Arr::get($this->info, 'max_value', 0);
        if ($max > 0) {
            $temp[] = 'max:' . $max;
        }
        $min = Arr::get($this->info, 'min_value');
        if (isset($min)) {
            $temp[] = 'min:' . $min;
        }
        $temp = array_merge($temp, $this->field->rules());
        $rules[$this->convert->getKey()] = $temp;
        return $rules;
    }
    public function setAttribute($attribute)
    {
        $attribute[$this->convert->getKey()] = $this->info->name;
        return $attribute;
    }
}
class StaticSingleConvert
{
    protected $key;
    public function __construct(string $key)
    {
        $this->key = $key;
    }
    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
    public function getValue($model)
    {
        return Arr::get($model, $this->key);
    }
    public function setValue($model,  $value)
    {
        Arr::set($model, $this->key, $value);
        return $model;
    }
}

function FieldCreator($type, $config)
{
    switch ($type) {
        case 'number':
            return new NumberField($config);
        case 'double':
            return new DoubleField($config);
        case 'boolean':
            return new BooleanField($config);
        case 'date':
            return new DateField($config);
        default:
            return new Field($config);
    }
}
class Field
{
    protected $config;
    public function __construct($config)
    {
        $this->config = $config;
    }
    public function getDefaultValue()
    {
        return $this->config['default_value'] ?? null;
    }
    public function rules()
    {
        return [];
    }
    public function convert($value, $model)
    {
        return $value;
    }
}
class TextField extends Field
{
    public function rules()
    {
        return ['string'];
    }
}
class NumberField extends Field
{
    public function rules()
    {
        return ['numeric'];
    }
}

class DoubleField extends Field
{
    public function rules()
    {
        return ['numeric'];
    }
}

class DateField extends Field
{
    // public function rules()
    // {
    //     return ['date_format:' . config('app.format_date')];
    // }
}
class BooleanField extends Field
{
    public function rules()
    {
        return ['boolean'];
    }
}
