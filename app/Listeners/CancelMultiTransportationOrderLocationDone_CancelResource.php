<?php

namespace App\Listeners;

use App\Events\CancelMultiTransportationOrderLocationDone;
use App\Models\Logistic\FleetTranOrderResource;

class CancelMultiTransportationOrderLocationDone_CancelResource
{
    /**
     * Handle the event.
     */
    public function handle(CancelMultiTransportationOrderLocationDone $event): void
    {

        $order_location_ids = $event->order_location_ids;
        FleetTranOrderResource::whereHas('location', function ($query) use ($order_location_ids) {
            $query->whereIn('id', $order_location_ids);
        })->update(['is_cancel' => true]);
    }
}
