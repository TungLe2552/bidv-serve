<?php

namespace App\Events;

use App\Models\Logistic\FleetTranOrderLocation;
use App\Models\Logistic\FleetTranOrderResource;
use App\Models\Logistic\FleetTransportOrder;
use App\Models\System\ResourceType;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Log;

class IChangeResourceForTranOrder
{

    /**
     *
     * @var FleetTransportOrder
     */
    public $order;
    /**
     *
     * @var ResourceType
     */
    public $resource_type;
    /**
     *
     * @var FleetTranOrderResource
     */
    public $resource;
    /**
     *
     * @var FleetTranOrderLocation
     */
    public $location;
}
