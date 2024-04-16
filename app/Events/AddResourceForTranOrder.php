<?php

namespace App\Events;

use App\Models\Logistic\FleetTranOrderLocation;
use App\Models\Logistic\FleetTranOrderResource;
use App\Models\Logistic\FleetTransportOrder;
use App\Models\System\ResourceType;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Log;

class AddResourceForTranOrder extends IChangeResourceForTranOrder
{
    use Dispatchable, SerializesModels;


    /**
     * Create a new event instance.
     * @param FleetTransportOrder $order
     * @param ResourceType $resource_type
     * @param FleetTranOrderLocation $location
     * @param FleetTranOrderResource $resource
     */
    public function __construct(FleetTransportOrder $order, ResourceType $resource_type, FleetTranOrderLocation $location, FleetTranOrderResource $resource)
    {
        $this->order = $order;
        $this->resource_type = $resource_type;
        $this->resource = $resource;
        $this->location = $location;
    }
}
