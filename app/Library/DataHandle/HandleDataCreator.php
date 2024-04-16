<?php

namespace App\Library\DataHandle;

use App\Library\DataHandle\Data\DataTypeHandle;
use ErrorException;

class HandleDataCreator
{
    private $product;
    public function __construct($type, $option)
    {
        switch ($type) {
            case 'table':
                $this->product = new DataTypeHandle($option);
                break;
            default:
                throw new ErrorException('Not found parser for type: ' . $type);
        }
    }
    public function getSubjectDisplay(): string
    {
        return $this->product->getSubjectDisplay();
    }
    public function getFields()
    {
        return $this->product->getFieldDb();
    }
    public function getSubject()
    {
        return $this->product->getSubject();
    }
    public function getHandle()
    {
        return $this->product;
    }

    public function updateOrCreate($options = [])
    {
        ['data_db' => $data_db, 'data_check' => $data_check] = $options;

        $data_return = ['is_create' => false, 'is_update' => false];
        if (count($data_check) > 0) {
            $model = $this->product->checkHasData($data_check);
        }
        if (isset($model)) {
            $model = $this->product->update($model, $data_db);
            $data_return['is_update'] = true;
        } else {
            $model = $this->product->create($data_db);
            $data_return['is_create'] = true;
        }
        $data_return['data'] = $model ?? null;
        return $data_return;
    }
}
