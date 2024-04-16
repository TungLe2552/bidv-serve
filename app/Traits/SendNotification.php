<?php

namespace App\Traits;

use App\Constants\ResourceTypeGroupConstant;
use App\Helpers\Dynamic\TableHelper;
use App\Helpers\OneSignalHelper;
use App\Models\Auth\User;
use App\Models\HR\Employee;
use App\Models\Logistic\FleetNotification;
use Carbon\Carbon;
use Ladumor\OneSignal\OneSignal;

trait SendNotification
{
    public function sendNotification(array $notification, $user_id)
    {
        $fields = [];
        $fields['include_external_user_ids'] = ["$user_id"];
        $fields['data'] = $notification;
        $fields['contents']['en'] = $notification['message'];
        OneSignal::sendPush($fields);
    }
    public function sendNotificationToUser($model, array $data, string $message = '')
    {
        // model là resource_type
        $table = TableHelper::getTable($model->model_id); // table vehicles hoặc employees
        $query = TableHelper::getQuery($table);
        $tmp = $query->findOrFail($data['resource_id']);
        $employee_id = $model->group == ResourceTypeGroupConstant::PHUONG_TIEN ? $tmp->driver_id : $tmp->id;
        $employee = Employee::where('id', $employee_id)->first();
        if ($employee) {
            $user = User::where('partner_id', $employee->partner_id)->first();
            if ($user) {
                $notification = FleetNotification::updateOrCreate([
                    "tran_order_id" => $data['tran_order_id'],
                    "resource_id" => $data['resource_id'],
                    "resource_type_id" => $model->id,
                    "user_id" => $user->id,
                ], [
                    "tran_order_id" => $data['tran_order_id'],
                    "resource_id" => $data['resource_id'],
                    "resource_type_id" => $model->id,
                    "user_id" => $user->id,
                    "tran_order_location_id" => $data['tran_order_location_id'] ?? null,
                    "tran_order_resource_id" => $data['tran_order_resource_id'] ?? null,
                    "message" => $message,
                    "time_notification" => Carbon::now(),
                ]);
                $notification = $notification->toArray();
                $fields = [];
                $fields['include_external_user_ids'] = ["$user->id"];
                $fields['data'] = $notification;
                $fields['contents']['en'] = $notification['message'];
                $typeNoti = $model->group == 'Phương tiện' ? 'driver' : 'worker';
                $helper = new OneSignalHelper();
                $notifi = $helper->sendNotification($typeNoti, $fields);
                return $notifi;
            }
        }
    }
}
