<?php

namespace App\Listeners;

use App\Events\RemoveResourceForTranOrder;
use App\Models\Logistic\FleetAddressReturnReference;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RemoveResourceForTranOrder_RemoveAddressReturnReference
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

        $order_resource = $event->resource;
        FleetAddressReturnReference::where('fleet_tran_order_resource_id', $order_resource->getKey())->delete();
    }
}
