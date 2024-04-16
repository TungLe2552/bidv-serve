<?php

namespace App\Events;

use App\Models\Logistic\FleetTranOrderLocation;
use App\Models\Logistic\FleetTranOrderLocationExtraDepartment;
use App\Models\Logistic\FleetTranOrderResource;
use App\Models\Logistic\FleetTransportOrder;
use App\Models\System\ResourceType;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class RemoveDepartmentForTranOrderDone
{
    use Dispatchable, SerializesModels;

    /**
     *
     * @var FleetTransportOrder
     */
    public $order;
    /**
     *
     * @var FleetTranOrderLocationExtraDepartment
     */
    public $department;
    /**
     *
     * @var FleetTranOrderLocation
     */
    public $location;
    /**
     * Create a new event instance.
     * @param FleetTransportOrder $order
     * @param FleetTranOrderLocation $location
     * @param FleetTranOrderLocationExtraDepartment $department
     */
    public function __construct(FleetTransportOrder $order, FleetTranOrderLocation $location, FleetTranOrderLocationExtraDepartment $department)
    {
        $this->order = $order;
        $this->department = $department;
        $this->location = $location;
    }
}
