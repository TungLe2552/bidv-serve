<?php

namespace App\Events;

use App\Models\Logistic\FleetTranOrderLocation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CancelMultiTransportationOrderLocationDone
{
    use Dispatchable, SerializesModels;

    /**
     *
     * @var Collection<FleetTranOrderLocation>
     */
    public $order_locations;
    public $order_location_ids;
    /**
     * Create a new event instance.
     * @param FleetTranOrderLocation $request
     */
    public function __construct($order_locations)
    {
        $this->order_locations = $order_locations;
        $this->order_location_ids = $order_locations->map(function ($model) {
            return $model->getKey();
        });
    }
}
