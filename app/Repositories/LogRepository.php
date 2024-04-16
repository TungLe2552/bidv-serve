<?php

namespace App\Repositories;

use App\Enum\LogActionMethodEnum;
use App\Helpers\Dynamic\TableHelper;
use App\Models\Auth\User;
use App\Models\Base\IrModel;
use App\Models\System\AudittrailsLog;
use App\Models\System\AudittrailsLogLine;
use App\Models\System\AudittrailsRule;
use Illuminate\Database\Eloquent\Model;

class LogRepository
{
    private static $instance;
    private $user;
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    /**
     * @param string $table table name
     * @param string $method method
     * @param callable(IrModel, string, AudittrailsRule): void $cb method
     */
    public function checkLog(string $table, string $method, callable $cb)
    {
        $is_valid = in_array($method, ['deleted', 'created', 'updated', 'read', 'export']);
        if (!$is_valid) {
            throw 'Không hỗ trợ lưu thao tác dạng: ' . $method;
        }
        $model = TableHelper::getTableByCode($table, false);
        if (empty($model)) {
            return;
        }
        //customize by C
        if ($table == 'fleet_transportation_requests') {
            $rule = AudittrailsRule::where('object_id', $model->id)->where(function ($query) {
                $query->whereNull('user_id');
            })->orderBy('user_id')->first();
        } else {
            $rule = AudittrailsRule::where('object_id', $model->id)->where('state', true)->where(function ($query) {
                $user = $this->user ?? auth()->user();
                $query->whereNull('user_id');
                $query->orWhere('user_id', $user->id);
            })->orderBy('user_id')->first();
        }

        if (empty($rule)) {
            return;
        }
        if (
            $rule->log_action
            || ($rule->log_create && $method == LogActionMethodEnum::CREATED->value)
            || ($rule->log_write && $method == LogActionMethodEnum::UPDATED->value)
            || ($rule->log_unlink && $method == LogActionMethodEnum::DELETED->value)
            || ($rule->og_read && $method == LogActionMethodEnum::READ->value)
            || ($rule->og_read && $method == LogActionMethodEnum::EXPORT->value)
        ) {
            $cb($model, $method, $rule);
        }
    }
    public function setUser($user)
    {
        $this->user = $user;
    }
    public function getMethodName(string $method)
    {
        $method_name = '';
        switch ($method) {
            case LogActionMethodEnum::DELETED->value:
                $method_name = "Xóa";
                break;
            case LogActionMethodEnum::CREATED->value:
                $method_name = "Thêm mới";
                break;
            case LogActionMethodEnum::UPDATED->value:
                $method_name = "Cập nhật";
                break;
            case LogActionMethodEnum::READ->value:
                $method_name = "Xem chi tiết";
                break;
            case LogActionMethodEnum::EXPORT->value:
                $method_name = "Xuất excel";
                break;
        }
        return $method_name;
    }
    public function getChanges($new_data, $old_data, string $method)
    {
        $result = [];
        if ($method == LogActionMethodEnum::DELETED) {
            $new_data = array_fill_keys(array_keys($old_data), 'Đã xóa dữ liệu');
        }
        if ($method == LogActionMethodEnum::CREATED->value) {
            $old_data = array_fill_keys(array_keys($new_data), 'Chưa có dữ liệu');
        }
        foreach ($new_data as $key => $value) {
            $old_value = $old_data[$key] ?? null;
            if ($old_value != $value) {
                if ($key !== 'updated_at' && $key !== 'created_at') {
                    $result[] = [
                        'key' => $key,
                        'oldValue' => $old_value,
                        'newValue' => $value,
                    ];
                }
            }
        }
        if ($method == LogActionMethodEnum::CREATED->value) {
            $result = array_values(array_filter($result, function ($item) {
                return $item['newValue'] !== null;
            }));
        }
        return $result;
    }
    public function logModel(IrModel $model, string $method, Model $new_data = null, Model $old_data = null)
    {
        $user = $this->user ?? auth()->user();
        $changes = $this->getChanges($new_data ? $new_data->getAttributes() : [], $old_data ? $old_data->getAttributes() : [], $method);
        $method_name = $this->getMethodName($method);
        $fields = $model->fields->mapWithKeys(function ($item) {
            return [$item->code => $item];
        });
        if (empty($changes) && $method != LogActionMethodEnum::READ->value && $method != LogActionMethodEnum::EXPORT->value) {
            return;
        }
        $log = AudittrailsLog::create([
            'name' => $model->name,
            'model_id' => $model->id,
            'user_id' => !empty($user) ? $user->id : User::where('login', 'administrator')->first()->id,
            'method' => $method_name,
            'resource_id' => $new_data->id??null,
            'timestamp' => now()
        ]);
        $log_lines = [];
        foreach ($changes as $value) {
            $field = $fields[$value['key']] ?? null;
            if (!empty($field)) {
                $log_lines[] = [
                    'field_id' => $field->id,
                    'log_id' => $log->id,
                    'old_value' => $value['oldValue'],
                    'new_value' => $value['newValue'],
                    'old_value_text' => $value['oldValue'],
                    'new_value_text' => $value['newValue'],
                    'field_description' => $value['key'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        if (count($log_lines) > 0) {
            AudittrailsLogLine::insert($log_lines);
        }
    }
}
