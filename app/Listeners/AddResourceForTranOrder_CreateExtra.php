<?php

namespace App\Listeners;

use App\Constants\ResourceTypeGroupConstant;
use App\Events\AddResourceForTranOrder;
use App\Models\Logistic\FleetTranOrderLocationExtraDepartment;
use App\Services\YeuCau\RequestDetailResourceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AddResourceForTranOrder_CreateExtra
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
    public function handle(AddResourceForTranOrder $event): void
    {
        $order = $event->order;
        $resource_type = $event->resource_type;
        $order_resource = $event->resource;
        $location = $event->location;
        $service = new RequestDetailResourceService();
        if ($resource_type->group == ResourceTypeGroupConstant::NHAN_SU) {
            $query = $service->getQueryOrderLocation($location, ResourceTypeGroupConstant::NHAN_SU);
            $data = $query->where('id', $order_resource->resource_id)->first();
            if (!empty($data) && !empty($data->department_id)) {
                FleetTranOrderLocationExtraDepartment::firstOrCreate([
                    'tran_order_location_id' => $location->getKey(),
                    'department_id' => $data->department_id
                ]);
            }
        }
    }
}
