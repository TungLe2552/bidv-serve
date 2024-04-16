<?php

namespace App\Listeners;

use App\Events\SendNotificationForTransportationOrder;
use App\Models\Logistic\FleetNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNotificationForTransportationOrder_SendForUserRelated
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
    public function handle(SendNotificationForTransportationOrder $event): void
    {
        $orders = $event->orders;
        foreach ($orders as $order) {
            $request = $order->request;
            $created_by_id = $request['created_by_id'] ?? null;
            $updated_by_id = $request['updated_by_id'] ?? null;
            $name = $request['name'] ?? '';
            $message = "$name đã được điều phối";
            if (!empty($created_by_id))
                FleetNotification::create([
                    "tran_order_id" => $order['id'],
                    "user_id" => $created_by_id,
                    "message" => $message,
                    'title' => 'Điều phối',
                    "content" => $message,
                    "time_notification" => Carbon::now(),
                ]);
            if (!empty($updated_by_id) && $created_by_id != $updated_by_id)
                FleetNotification::create([
                    "tran_order_id" => $order['id'],
                    "user_id" => $updated_by_id,
                    "message" => $message,
                    'title' => 'Điều phối',
                    "content" => $message,
                    "time_notification" => Carbon::now(),
                ]);
        }
    }
}
