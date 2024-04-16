<?php

namespace App\Listeners;

use App\Constants\ResourceTypeGroupConstant;
use App\Events\SendNotificationForTransportationOrder;
use App\Helpers\Notification\TransportationRequestNotificationHelper;
use App\Helpers\OneSignalHelper;
use App\Models\Logistic\FleetNotification;
use App\Models\Logistic\FleetTranOrderResource;
use App\Services\YeuCau\RequestDetailResourceService;
use Carbon\Carbon;

class SendNotificationForTransportationOrder_SendForResource
{
    public function handle(SendNotificationForTransportationOrder $event): void
    {
        $orders  = $event->orders;
        $query = FleetTranOrderResource::where('notification_sent', false);
        $query->whereHas('location.transportOrder', function ($query) use ($orders) {
            $query->whereIn('id', array_map(function ($item) {
                return $item['id'];
            }, $orders));
        });
        $query->with('resourceType', 'location.transportOrder', 'location.transportOrder.request.type:id,name', 'location.transportOrder.request.shift:id,name', 'location.detail', 'location.address:id,name');
        $resources = $query->get();
        $service = new RequestDetailResourceService();
        $helper = new OneSignalHelper();
        $cache = [];
        foreach ($resources as $resource) {
            $key_cache = ($resource->resourceType->group ?? '') . ' ' . $resource->resource_id . ' ' . $resource->location->delivery_order_id;
            if (empty($cache[$key_cache])) {
                $cache[$key_cache] = [];
            }
            $cache[$key_cache][] = $resource;
        }
        foreach ($cache as $resources) {
            $resource = $resources[0];
            $location = $resource['location'];
            $order = $location['transportOrder'];
            $tran_request = $order->request;
            $resource_type = $resource['resourceType'];
            $resource_id = $resource->resource_id;
            $tile = 'Điều phối';
            $message = "Bạn được giao thực hiện lệnh " . TransportationRequestNotificationHelper::getTitle($tran_request);
            $content = $message;
            if ($resource->resourceType->group == ResourceTypeGroupConstant::PHUONG_TIEN) {
                $key_time = 'start_time_driver';
            } else
            if ($resource->resourceType->group == ResourceTypeGroupConstant::NHAN_SU) {
                $key_time = 'start_time_worker';
            }
            foreach ($resources as $resource) {
                $location = $resource['location'];
                $address_name = $location['address']['name'];
                $notes2 = $location['notes2'];
                $notes3 = $location['notes3'];
                $time = $location['detail'][$key_time] ?? '';
                $text = $address_name;
                if (!empty($time)) {
                    $text .= " - XP: " . $time;
                }
                $text .= " - HH: $notes2 - KL: $notes3";
                $content .= PHP_EOL . $text;
            }
            $extra_info = $service->getInfoForResource($resource);
            if (!empty($extra_info)) {
                $user_id = $extra_info['user_id'];
                $type_notify = $extra_info['type_notify'];

                $notification = FleetNotification::create([
                    "tran_order_id" => $order['id'],
                    'tran_order_resource_id' => $resource['id'],
                    "resource_type_id" => $resource_type['id'],
                    "resource_id" => $resource_id,
                    "user_id" => $user_id,
                    'title' => $tile,
                    "tran_order_location_id" => $location['id'],
                    "message" => $message,
                    "content" => $content,
                    "time_notification" => Carbon::now(),
                ]);
                if (isset($type_notify)) {
                    $fields = [];
                    $fields['include_external_user_ids'] = ["$user_id"];
                    $fields['data'] = $notification;
                    $fields['contents']['en'] = $notification['message'];
                    $helper->sendNotification($type_notify, $fields);
                }
            }
            foreach ($resources as $resource) {
                $resource->notification_sent = true;
                $resource->save();
            }
        }
    }
}
