<?php

namespace App\Listeners;

use App\Constants\ResourceTypeGroupConstant;
use App\Events\UpdateTransportationRequestDone;
use App\Helpers\OneSignalHelper;
use App\Models\Logistic\FleetNotification;
use App\Models\Logistic\FleetTranOrderResource;
use App\Services\YeuCau\RequestDetailResourceService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateTransportationRequestDone_SendForDriver
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
    public function handle(UpdateTransportationRequestDone $event): void
    {

        $type_notify = 'web';
        $helper = new OneSignalHelper();
        $tran_request = $event->tran_request;
        if (empty($tran_request->order)) {
            return;
        }
        $type_request = $tran_request['type'] ?? null;
        $type_request_name = isset($type_request) ? $type_request['name'] : '';
        $date = (new Carbon($tran_request['start_date']))->format('d/m/Y');
        $shift_name = isset($shift) ? $shift['name'] : '';
        $message = "Yêu cầu vận tải $type_request_name - $date - $shift_name được cập nhật";
        $context = "$type_request_name - $date - $shift_name";
        $info = [
            "message" => $message,
            'title' => 'Yêu cầu vận tải được cập nhật',
            'content' => $context,
            'options' => json_encode([
                'tran_request_id' => $tran_request->getKey(),
                'create_by_id' => $tran_request->created_by_id,
                'request_date' => $tran_request['start_date'],
            ]),
            "time_notification" => Carbon::now(),
        ];

        $order  = $tran_request->order;
        $query = FleetTranOrderResource::where('notification_sent', true);
        $query->whereHas('location.transportOrder', function ($query) use ($order) {
            $query->where('id', $order->getKey());
        })->whereHas('resourceType', function ($query) use ($order) {
            $query->where('group', ResourceTypeGroupConstant::PHUONG_TIEN);
        });
        $resources = $query->get();
        $service = new RequestDetailResourceService();
        $helper = new OneSignalHelper();
        $cache = [];
        foreach ($resources as $resource) {
            $location = $resource['location'];
            $resource_type = $resource['resourceType'];
            $resource_id = $resource->resource_id;
            $key_cache = ($resource->resourceType->group ?? '') . ' ' . $resource->resource_id . ' ' . $resource->location->delivery_order_id;
            if (empty($cache[$key_cache])) {
                $cache[$key_cache] = true;
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
                        "tran_order_location_id" => $location['id'],
                        "message" => $message,
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
            $resource->notification_sent = true;
            $resource->save();
        }
    }
}
