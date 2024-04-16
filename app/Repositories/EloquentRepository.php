<?php

namespace App\Repositories;

abstract class EloquentRepository implements RepositoryInterface
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $_model;
    protected function model()
    {
        return $this->_model;
    }
    public function __construct()
    {
        $this->setBaseModel();
        $this->setDefaultOption(['id' => null]);
        $this->init();
    }

    /**
     * get model
     * @return string
     */
    abstract public function getModel();

    public function init()
    {
    }
    public function setBaseModel()
    {
        $this->_model = app()->make(
            $this->getModel()
        );
    }
    public function find($id)
    {
        $result = $this->getModel()::findOrFail($id);
        return $result;
    }
    protected $defaultOption = [];
    public function setDefaultOption($option)
    {
        $this->defaultOption = array_merge($this->defaultOption, $option);
    }
    public function withLogHandler(string $method, $option, $callback)
    {
        $option = array_merge($this->defaultOption, $option);
        $id = $option['id'];
        if (isset($option['getModel'])) {
            $model = $option['getModel']($id);
        } else {
            if (isset($id)) {
                $model = $this->find($id);
            } else {
                $model = app()->make(
                    $this->getModel()
                );
            }
        }
        if ($method != 'update') {
            $old_data = clone $model;
        }
        $result = $callback($model);
        $table = $model->getTable();
        $log = LogRepository::getInstance();
        $new_data = $result;
        $log->checkLog($table, $method, function ($model, $method) use ($new_data, $old_data, $log) {
            $log->logModel($model, $method, $new_data, $old_data);
        });

        return $result;
    }
}
