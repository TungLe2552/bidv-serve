<?php

namespace App\Module\Field;

use Arr;
use Carbon\Carbon;

class FieldFactory
{

    static function get($field)
    {
        $handle = null;
        $RULES = [
            [
                'check' => fn ($field) => $field['selectable'] && !empty($field['selection']),
                'handle' => fn ($field) => new SelectField($field)
            ],
            [
                'check' => fn ($field) => $field['type'] == 'boolean' && $field['code'] == 'active',
                'handle' => fn ($field) => new ActiveField($field)
            ],
            [
                'check' => fn ($field) => $field['type'] == 'boolean',
                'handle' => fn ($field) => new BooleanField($field)
            ],
            [
                'check' => fn ($field) => $field['type'] == 'date',
                'handle' => fn ($field) => new DateField($field)
            ]
        ];
        foreach ($RULES as $rule) {
            if ($rule['check']($field)) {
                $handle = $rule['handle'];
                break;
            }
        }
        $handle = $handle ?? fn ($field) => new AField($field);
        return $handle($field);
    }
}
class AField
{
    protected $info;

    public function __construct($field)
    {
        $this->info = $field;
    }
    public function getCode()
    {
        return $this->info['code'] ?? '';
    }
    public function getLabel()
    {
        return $this->info['name'] ?? '';
    }
    public function getValueExport($data)
    {
        $value = Arr::get($data, $this->getCode());
        return $value;
    }
}
class DateField extends AField
{

    public function getValueExport($data)
    {
        $value = parent::getValueExport($data);
        if ($value) {
            return Carbon::parse($value)->format('d/m/Y');
        }
    }
}
class ActiveField extends AField
{

    public function getValueExport($data)
    {
        $value = parent::getValueExport($data);
        return $value ? 'Hoạt động' : 'Không hoạt động';
    }
}
class BooleanField extends AField
{

    public function getValueExport($data)
    {
        $value = parent::getValueExport($data);
        return $value ? 'Có' : 'Không';
    }
}
class SelectField extends AField
{
    public function getItems()
    {
        return $this->info['selection'] ?? [];
    }
    public function getValueExport($data)
    {
        $value = parent::getValueExport($data);
        $field = $this->info;
        $items = $this->getItems();
        $propsOption =  $field['propsInput'] ?? [];
        foreach ($items as $item) {
            if (getValue($item, $propsOption, "text_value", "value") === $value) {
                break;
            }
        }
        $label = getValue($item, $propsOption, "text_label", "text");
        return $label ?? '';
    }
}
function getValue($item, $option, $key, $default_key)
{
    $result = $item ?? "";
    if (strpos($key, "$") !== false) {
        return replacer($key, $item);
    }
    if ($item && is_array($item)) {
        $result = $item;
        $result = json_encode($result);
        $key_option = $option[$key] ?? $default_key;
        if (strpos($key_option, "$") !== false) {
            return replacer($key_option, $item);
        }
        $result = $item[$key_option] ?? $item[$default_key] ?? null;
    }
    return $result;
}
function replacer($tpl, $data)
{
    return preg_replace_callback('/\$\(([^\)]+)?\)/', function ($matches) use ($data) {
        $key = $matches[1];
        if (is_string($key) && strpos($key, "|") !== false) {
            $array = explode("|", $key);
            return array_reduce($array, function ($acc, $cur) use ($data) {
                if ($acc === null) {
                    $acc = $data[$cur] ?? null;
                }
                return $acc;
            });
        }

        return $data[$key] ?? "";
    }, $tpl);
}
