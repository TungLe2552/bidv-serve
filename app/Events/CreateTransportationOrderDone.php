<?php

namespace App\Events;

use App\Models\Logistic\FleetTransportationRequest;
use App\Models\Logistic\FleetTransportOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateTransportationOrderDone
{
    use Dispatchable, SerializesModels;

    /**
     *
     * @var FleetTransportOrder
     */
    public $tran_order;
    /**
     *
     * @var FleetTransportationRequest
     */
    public $tran_request;
    /**
     * Create a new event instance.
     * @param FleetTransportOrder $request
     */
    public function __construct(FleetTransportOrder $tran_order)
    {
        $this->tran_order = $tran_order;
        $this->tran_request = $tran_order->request;
        $this->tran_request->load('locations.detailResources.resourceType', 'type', 'shift');
    }
}
