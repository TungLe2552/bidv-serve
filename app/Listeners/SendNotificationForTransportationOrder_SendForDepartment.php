<?php

namespace App\Listeners;

use App\Constants\TypeNotification;
use App\Events\SendNotificationForTransportationOrder;
use App\Helpers\Notification\TransportationRequestNotificationHelper;
use App\Helpers\OneSignalHelper;
use App\Models\Logistic\FleetNotification;
use App\Models\Logistic\FleetTranOrderLocationExtraDepartment;
use Carbon\Carbon;

class SendNotificationForTransportationOrder_SendForDepartment
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SendNotificationForTransportationOrder $event): void
    {
        $orders = $event->orders;
        $query = FleetTranOrderLocationExtraDepartment::where('notification_sent', false)->with('location.address:id,name', 'location.transportOrder', 'location.detail', 'location.transportOrder.request.type:id,name', 'location.transportOrder.request.shift:id,name');
        $query->whereHas('location.transportOrder', function ($query) use ($orders) {
            $query->whereIn('id', array_map(function ($item) {
                return $item['id'];
            }, $orders));
        });
        $query->leftJoin('materialized_user_for_employee_department', function ($join) {
            $join->on('materialized_user_for_employee_department.department_id', 'fleet_tran_order_location_extra_departments.department_id');
        });
        $query->select(['fleet_tran_order_location_extra_departments.department_id', 'tran_order_location_id', 'user_id', 'fleet_tran_order_location_extra_departments.id', 'number_worker']);
        $extra_departments = $query->get();
        $helper = new OneSignalHelper();
        $cache = [];
        foreach ($extra_departments as $resource) {
            $key_cache = $resource->department_id . ' ' . $resource->location->delivery_order_id;
            if (empty($cache[$key_cache])) {
                $cache[$key_cache] = [];
            }
            $cache[$key_cache][] = $resource;
        }
        foreach ($cache as $key => $departments) {
            $extra_department = $departments[0];
            $location = $extra_department['location'];
            $time = $location['detail']['start_time_worker'] ?? '';
            $order = $location['transportOrder'];
            $tran_request = $order['request'] ?? null;
            $user_id = $extra_department['user_id'] ?? null;

            if (isset($user_id)) {
                $message = "Tổ phụ trách được giao thực hiện lệnh " . TransportationRequestNotificationHelper::getTitle($tran_request);
                $content = $message;
                foreach ($departments as $resource) {
                    $location = $resource['location'];
                    $address_name = $location['address']['name'];
                    $notes2 = $location['notes2'];
                    $notes3 = $location['notes3'];
                    $time = $location['detail']['start_time_worker'] ?? '';
                    $sl = $resource['number_worker'] ?? '';
                    $text = $address_name;
                    if (!empty($time)) {
                        $text .= " - XP: " . $time;
                    }
                    if (!empty($sl)) {
                        $text .= " - SL: " . $sl;
                    }
                    $text .= " - HH: $notes2 - KL: $notes3";
                    $content .= PHP_EOL . $text;
                }
                $notification = FleetNotification::create([
                    "tran_order_id" => $order['id'],
                    "user_id" => $user_id,
                    'title' => 'Điều phối',
                    'content' => $content,
                    "tran_order_location_id" => $location['id'],
                    "message" => $message,
                    "time_notification" => Carbon::now(),
                ]);
                $fields = [];
                $fields['include_external_user_ids'] = ["$user_id"];
                $fields['data'] = $notification;
                $fields['contents']['en'] = $notification['message'];
                $helper->sendNotification(TypeNotification::WORKER, $fields);
            }
            foreach ($departments as $department) {
                $department->notification_sent = true;
                $department->save();
            }
        }
    }
}
