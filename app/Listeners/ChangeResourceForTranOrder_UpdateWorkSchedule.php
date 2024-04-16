<?php

namespace App\Listeners;

use App\Events\IChangeResourceForTranOrder;
use App\Models\HR\WorkScheduleLine;
use App\Models\Logistic\FleetTranOrderResource;

class ChangeResourceForTranOrder_UpdateWorkSchedule
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
    public function handle(IChangeResourceForTranOrder $event): void
    {

        $order = $event->order;
        $resource_type = $event->resource_type;
        $order_resource = $event->resource;
        $location = $event->location;
        $resource_type_id = $order_resource->resource_type_id;
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
            $state = $count > 1 ? '2-Trùng lịch' : '1-Sẵn sàng';
            $notes = $count > 0 ? $order->code : "";
            $number_work_assigned = $count;
            $work_schedule = WorkScheduleLine::where('date', $order->start_date)->where('shift_id', $order->shift_id)->whereHas('schedule', function ($query) {
                $query->active();
            })->where('resource_id', $resource_id)->whereHas('resourceType', function ($query) use ($resource_type) {
                $query->where('group', $resource_type->group);
            });
            $old_note = $work_schedule->get()->toArray()[0]['notes'];
            if ($old_note && $count > 0) {
                $split_old_notes = explode('/', $old_note);

                if ($count < count($split_old_notes) && in_array($notes, $split_old_notes)) {
                    $index = array_search($notes, $split_old_notes);
                    unset($split_old_notes[$index]);
                    $notes = implode('/', $split_old_notes);
                    $notes = $notes;
                } else if ($count >= count($split_old_notes) && !in_array($notes, $split_old_notes)) {
                    $notes = $old_note . '/' . $notes;
                } else if ($count >= count($split_old_notes) && in_array($notes, $split_old_notes)) {
                    $notes = '';
                    $notes = $old_note;
                }
            }
            if ((!$old_note && $count > 1) || $count === 0) {
                $notes = '';
            }
            $work_schedule->update(['state' => $state, 'number_work_assigned' => $number_work_assigned, 'notes' => $notes]);
        }
    }
}
