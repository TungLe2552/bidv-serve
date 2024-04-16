<?php

namespace App\Events;

use App\Models\Logistic\FleetTranOrderLocation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CancelTransportationOrderLocationDone
{
    use Dispatchable, SerializesModels;

    /**
     *
     * @var FleetTranOrderLocation
     */
    public $order_location;
    /**
     * Create a new event instance.
     * @param FleetTranOrderLocation $request
     */
    public function __construct(FleetTranOrderLocation $order_location)
    {
        $this->order_location = $order_location;
    }
}
