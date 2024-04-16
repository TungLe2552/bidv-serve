<?php

namespace App\Events;

use App\Models\Logistic\FleetTransportOrder;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendNotificationForTransportationOrder
{
    use Dispatchable,  SerializesModels;

    /**
     *
     * @var FleetTransportOrder[]
     */
    public $orders;
    /**
     * Create a new event instance.
     * @param FleetTransportOrder[] $orders
     */
    public function __construct(array $orders)
    {
        $this->orders = $orders;
    }
}
