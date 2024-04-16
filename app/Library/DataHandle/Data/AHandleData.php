<?php

namespace App\Library\DataHandle\Data;

use App\Scopes\HideGeometryScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

abstract class AHandleData implements IHandleData
{
    /**
     * @return Builder
     */
    abstract public function query();
    abstract public function getSubjectDisplay(): string;
    abstract public function getSubject();

    public function checkHasData(array $data_check)
    {
        if (count($data_check) > 0) {
            return $this->query()->where($data_check)->first();
        }
    }
    public function update($model, $data_db)
    {
        $model->update($data_db);
        return $model;
    }
    public function create($data_db)
    {
        return $this->query()->create($data_db);
    }
}
