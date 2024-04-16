<?php

namespace App\Listeners;

use App\Events\IChangeResourceForTranOrder;
use App\Traits\SendNotification;

class RemoveResourceForTranOrder_SendNotifyForRemove
{
    use SendNotification;
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
    public function handle(IChangeResourceForTranOrder $event): void
    {
        $order = $event->order;
        $resource_type = $event->resource_type;
        $order_resource = $event->resource;
        $location = $event->location;
        $info = [
            "tran_order_location_id" => $order_resource->fleet_tran_order_location_id ?? null,
            "tran_order_id" => $location->delivery_order_id ?? null,
            "resource_id" => $order_resource->resource_id,
        ];
        if ($order_resource->notification_sent) {
            $message = $location->address->name ? 'Công việc của bạn tại ' . $location->address->name . ' đã bị hủy' : 'Công việc của bạn đã bị hủy';
            $this->sendNotificationToUser($resource_type, $info, $message);
        }
    }
}
