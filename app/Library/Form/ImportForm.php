<?php

namespace App\Library\Form;

use App\Library\Form\FieldHelper;
use Arr;

class ImportForm
{
    private $fields = [];
    public function __construct($field_import, $field_dbs)
    {
        foreach ($field_import as $field) {
            $field_db = $field_dbs->first(function ($item) use ($field) {
                return $item->canHandle($field['field_db']);
            });
            $this->addField($field_db, $field);
        }
    }

    private function addField($field_db, $config = [])
    {
        $field_format = FieldHelper::getFieldByType($field_db['type'], $field_db, $config);
        $field = new TypeField($field_db, $field_format, $config);
        $this->fields[] = $field;
    }
    public function convertData($data)
    {
        $result = [];
        $result_check = [];
        foreach ($this->fields as $field) {
            $result = $field->convertData($result, $data);
            if ($field->isCheck()) {
                $result_check = $field->convertData($result_check, $data);
            }
        }
        return ['data_db' => $result, 'data_check' => $result_check];
    }
}
class TypeField
{
    protected $info;
    protected $config;
    protected $format;
    protected $field_import;

    protected $field_format;
    public function isMulti()
    {
        return $this->field_format->isMulti();
    }
    public function isCheck()
    {
        return $this->config['check'] ?? false;
    }
    public function __construct($field_db, $field_format, $config = [])
    {
        $this->info = $field_db;
        $this->config = $config;
        $this->format = $field_format->getFormat();
        $this->field_format = $field_format;
        $this->field_import = $config['field_import'] ?? null;
    }

    public function convertData($data_import, $data_db)
    {
        if ($this->isMulti()) {
            $data_import = $this->field_format->getDataExport($data_import, $data_db);
        } else {
            $value = Arr::get($data_db, $this->field_import);
            if ($this->field_format->isComplex()) {
                $value = $this->field_format->convertDataImport($value, $this->config);
            }
            $data_import = $this->format->setValue(
                $data_import,
                $value,
            );
        }
        return $data_import;
    }
}
