<?php

namespace App\Events;

use App\Models\Logistic\FleetTransportationRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CancelDieuPhoi extends IChangeResourceForTranOrder
{
    use Dispatchable, SerializesModels;

    /**
     *
     * @var FleetTransportationRequest
     */
    public $tran_request;
    /**
     * Create a new event instance.
     * @param FleetTransportationRequest $request
     */
    public function __construct(FleetTransportationRequest $tran_request)
    {
        $this->tran_request = $tran_request;

    }
}
