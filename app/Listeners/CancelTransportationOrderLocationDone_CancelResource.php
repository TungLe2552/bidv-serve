<?php

namespace App\Listeners;

use App\Events\CancelTransportationOrderLocationDone;
use App\Models\Logistic\FleetTranOrderResource;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CancelTransportationOrderLocationDone_CancelResource
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
    public function handle(CancelTransportationOrderLocationDone $event): void
    {
        $location = $event->order_location;
        FleetTranOrderResource::whereHas('location', function ($query) use ($location) {
            $query->where('id', $location->getKey());
        })->update(['is_cancel' => true]);
    }
}
