<?php

namespace App\Library\DataHandle\Data;

use App\Helpers\Dynamic\TableHelper;
use App\Models\Base\IrModel;
use Illuminate\Database\Eloquent\Builder;

class DataTypeHandle extends AHandleData
{
    /**
     * data type
     * @var  IrModel $data_type
     */

    protected $data_type;
    public function __construct($options)
    {
        ['id' => $data_type_id] = $options;
        $this->data_type = TableHelper::getTable($data_type_id);
    }
    public function getFieldDb()
    {
        return $this->data_type->fields;
    }
    public function query(): Builder
    {
        return TableHelper::getQuery($this->data_type);
    }
    public function getSubjectDisplay(): string
    {
        return $this->data_type->name;
    }
    public function getSubject(): IrModel
    {
        return  $this->data_type;
    }
}
