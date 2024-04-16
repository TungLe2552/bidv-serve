<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BaseRepository extends EloquentRepository
{
    private $class;
    public function __construct(string $class, $defaultOption = [])
    {
        $this->class = $class;
        parent::__construct();
        $this->setDefaultOption(array_merge(['logname' => 'system', 'description' => '', 'forceLog' => false, 'disableLog' => false], $defaultOption));
    }
    public function getModel()
    {
        return $this->class;
    }
    public function validate($type, $info, $id = null)
    {
        $this->class::validate($type, $info, $id);
    }
    public function create(array $attributes, callable $cb = null, callable $cb_after = null): Model
    {
        return DB::transaction(
            function () use ($attributes, $cb, $cb_after) {
                return $this->withLogHandler('created', [], function ($model) use ($attributes, $cb, $cb_after) {
                    $model = $model->fill($attributes);
                    if (isset($cb)) {
                        $cb($model, $attributes);
                    }

                    $model->save();
                    if (isset($cb_after)) {
                        $cb_after($model, $attributes);
                    }
                    return $model;
                });
            }
        );
    }

    public function update($id, array $attributes, callable $cb = null, callable $cb_after = null): Model
    {
        return DB::transaction(
            function () use ($id, $attributes, $cb,  $cb_after) {
                return $this->withLogHandler('updated', ['id' => $id], function ($model) use ($attributes, $cb, $cb_after) {
                    $old = $model->replicate();
                    $model->fill($attributes);
                    if (isset($cb)) {
                        $cb($model, $old, $attributes);
                    }
                    $model->save();
                    if (isset($cb_after)) {
                        $cb_after($model, $attributes);
                    }
                    return $model;
                });
            }
        );
    }

    public function delete($id, callable $cb = null, callable $cb_after = null): Model
    {
        return DB::transaction(
            function () use ($id, $cb,  $cb_after) {
                return $this->withLogHandler('deleted', ['id' => $id], function ($model) use ($cb, $cb_after) {
                    if (isset($cb)) {
                        $cb($model);
                    }

                    $model->delete();
                    if (isset($cb_after)) {
                        $cb_after($model);
                    }
                    return $model;
                });
            }
        );
    }
    public function show($id): Model
    {
        return $this->withLogHandler('read', ['id' => $id], function ($model) {
            return $model;
        });
    }
}
