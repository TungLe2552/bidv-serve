<?php

namespace App\Listeners;

use App\Constants\ResourceTypeGroupConstant;
use App\Events\RemoveResourceForTranOrder;
use App\Models\Logistic\FleetDoNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RemoveResourceForTranOrder_RemoveDoNotification
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
    public function handle(RemoveResourceForTranOrder $event): void
    {
        $order = $event->order;
        $resource_type = $event->resource_type;
        $order_resource = $event->resource;
        $location = $event->location;
        if ($resource_type->group == ResourceTypeGroupConstant::PHUONG_TIEN) {
            $fleet_vehicle_id = $order_resource->resource_id;
            FleetDoNotification::where([
                'shift_id' => $order->shift_id,
                'req_location_id' => $order_resource->req_detail_resource_id,
                'tran_order_location_id' => $order_resource->fleet_tran_order_location_id,
                'destination_id' => $location->destination_id,
                'fleet_vehicle_id' => $fleet_vehicle_id
            ])->delete();
        }
    }
}
