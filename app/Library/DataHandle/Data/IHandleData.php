<?php

namespace App\Library\DataHandle\Data;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface IHandleData
{
    /**
     * @return Collection<IFile>
     */
    public function getFieldDb();
    /**
     * @return Builder
     */
    public function query();
    public function getSubjectDisplay(): string;
    public function getSubject();
    public function checkHasData(array $data_check);
    public function update($model, $data_db);
    public function create($data_db);
}
