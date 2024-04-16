<?php

namespace App\Listeners;

use App\Events\CancelDieuPhoi;
use App\Models\HR\WorkScheduleLine;
use App\Models\Logistic\FleetTranOrderResource;
use App\Models\System\ResourceType;

class CancelDieuPhoi_UpdateWorkSchedule
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
    public function handle(CancelDieuPhoi $event): void
    {
        $tran_req = $event->tran_request;
        $order = $tran_req->order;
        if ($order) {
            $locations = $order->locations;
            foreach ($locations as $location) {
                $order_resources = $location->resources;
                foreach ($order_resources as $order_resource) {
                    $resource_type = ResourceType::find($order_resource->resource_type_id);
                    $resource_id = $order_resource->resource_id;
                    $count = FleetTranOrderResource::whereHas('resourceType', function ($query) use ($resource_type) {
                        $query->where('group', $resource_type->group);
                    })->where('resource_id', $resource_id)->whereHas('tranOrderLocation.transportOrder', function ($query) use ($order) {
                        $query->where('start_date', $order->start_date);
                        $query->where('shift_id', $order->shift_id);
                    })->join('fleet_tran_order_locations', 'fleet_tran_order_location_id', 'fleet_tran_order_locations.id')->select('delivery_order_id')->distinct('delivery_order_id')->count();
                    $schedule_line = WorkScheduleLine::where('date', $order->start_date)->where('shift_id', $order->shift_id)->whereHas('schedule', function ($query) {
                        $query->active();
                    })->where('resource_id', $resource_id)->where('resource_type_id', $resource_type->getKey())->first();
                    if (
                        isset($schedule_line) &&
                        $schedule_line->state != '3-Nghỉ'
                    ) {
                        $state = '1-Sẵn sàng';
                        $notes = $order->code;
                        $work_schedule = WorkScheduleLine::where('date', $order->start_date)->where('shift_id', $order->shift_id)->whereHas('schedule', function ($query) {
                            $query->active();
                        })->where('resource_id', $resource_id)->whereHas('resourceType', function ($query) use ($resource_type) {
                            $query->where('group', $resource_type->group);
                        });

                        $old_note = $work_schedule->get()->toArray()[0]['notes'];
                        $number_work_assigned = $work_schedule->first()->number_work_assigned;
                        if ($number_work_assigned > 0) {
                            $number_work_assigned--;
                        }

                        if ($old_note) {
                            $split_old_notes = explode('/', $old_note);
                            if (in_array($notes, $split_old_notes)) {
                                $index = array_search($notes, $split_old_notes);
                                unset($split_old_notes[$index]);
                                $notes = implode('/', $split_old_notes);
                                $notes = $notes;
                            }
                        }
                        $work_schedule_update = WorkScheduleLine::where('date', $order->start_date)->where('shift_id', $order->shift_id)->whereHas('schedule', function ($query) {
                            $query->active();
                        })->where('resource_id', $resource_id)->whereHas('resourceType', function ($query) use ($resource_type) {
                            $query->where('group', $resource_type->group);
                        });
                        if ($number_work_assigned <= 1) {
                            $work_schedule_update->update(['state' => $state, 'number_work_assigned' => $number_work_assigned, 'notes' => $notes]);
                        } else {
                            $work_schedule_update->update(['number_work_assigned' => $number_work_assigned, 'notes' => $notes]);
                        }
                    }
                }
            }
        }
    }
}
