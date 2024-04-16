<?php

namespace App\Listeners;

use App\Events\CancelTransportationRequestDone;
use App\Models\Logistic\FleetTranOrderLocation;

class CancelTransportationRequestDone_CancelTransportationOrder
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
    public function handle(CancelTransportationRequestDone $event): void
    {
        $tran_request = $event->tran_request;
        $order = $tran_request->order;
        if (empty($order)) {
            return;
        }

        $order->load('locations.resources');
        $order->is_deleted = true;
        $order->save();
        $locations = $order->locations;
        $is_dieu_phoi = $locations->contains(function (FleetTranOrderLocation $location) {
            return $location->resources->count() > 0;
        });
        if ($is_dieu_phoi) {
            return;
        }
        // nếu chưa điều phối thì tự động hủy toàn bộ liên quan, TH2
        FleetTranOrderLocation::whereHas('transportOrder', function ($query) use ($order) {
            $query->where('id', $order->id);
        })->update(['is_cancel' => true]);
    }
}
