<?php

namespace App\Listeners;

use App\Constants\ResourceTypeGroupConstant;
use App\Events\AddResourceForTranOrder;
use App\Models\Logistic\FleetDoNotification;
use Carbon\Carbon;
use Log;

class AddResourceForTranOrder_CreateDoNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(AddResourceForTranOrder $event): void
    {
        $order = $event->order;
        $resource_type = $event->resource_type;
        $order_resource = $event->resource;
        $location = $event->location;
        $fleet_vehicle_id = null;
        if ($resource_type->group == ResourceTypeGroupConstant::PHUONG_TIEN) {
            $fleet_vehicle_id = $order_resource->resource_id;
            FleetDoNotification::create([
                'shift_id' => $order->shift_id,
                'req_location_id' => $location->req_detail_resource_id,
                'tran_order_location_id' => $order_resource->fleet_tran_order_location_id,
                'req_date' => Carbon::now()->format('Y-m-d'),
                'tran_order_date' => $order->start_date,
                'notification_date' => Carbon::now()->format('Y-m-d'),
                'destination_id' => $location->destination_id,
                'fleet_vehicle_id' => $fleet_vehicle_id
            ]);
        }
    }
}
