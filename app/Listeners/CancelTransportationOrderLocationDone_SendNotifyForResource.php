<?php

namespace App\Listeners;

use App\Constants\ResourceTypeGroupConstant;
use App\Events\CancelTransportationOrderLocationDone;
use App\Helpers\Notification\TransportationRequestNotificationHelper;
use App\Helpers\OneSignalHelper;
use App\Models\Logistic\FleetNotification;
use App\Models\Logistic\FleetTranOrderResource;
use App\Services\YeuCau\RequestDetailResourceService;
use Carbon\Carbon;

class CancelTransportationOrderLocationDone_SendNotifyForResource
{
    public function handle(CancelTransportationOrderLocationDone $event): void
    {
        $location = $event->order_location;
        $location->load('transportOrder.shift', 'transportOrder.request.type');
        $query = FleetTranOrderResource::where('notification_sent', true);
        $query->whereHas('location', function ($query) use ($location) {
            $query->where('id', $location->getKey());
        });
        $service = new RequestDetailResourceService();
        $resources = $query->get();
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
            $tile = 'Hủy Điều phối';
            $message = "Nhiệm vụ " . TransportationRequestNotificationHelper::getTitle($tran_request) . " đã bị hủy";
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
        }
    }
}
