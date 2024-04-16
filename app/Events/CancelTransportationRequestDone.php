<?php

namespace App\Events;

use App\Models\Logistic\FleetTransportationRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CancelTransportationRequestDone
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
        $tran_request->load('order');
        $this->tran_request = $tran_request;
    }
}
