<?php

namespace App\Listeners;

use App\Events\SendNotificationForTransportationOrder;
use App\Helpers\OneSignalHelper;
use App\Models\Auth\User;
use App\Models\BA\Customer;
use App\Models\HR\Employee;
use App\Models\Logistic\FleetNotification;
use App\Models\Logistic\FleetTranOrderResource;
use Carbon\Carbon;

class SendNotificationForTransportationOrder_SendForSalesMan
{
    public function handle(SendNotificationForTransportationOrder $event): void
    {
        $orders = $event->orders;
        $helper = new OneSignalHelper();

        foreach ($orders as $val) {
            $order = $val->toArray();
            $tile = 'Điều phối';
            $userId = $order['request']['created_by_id'];
            $resources = FleetTranOrderResource::where('notification_sent', false)->with(['tranOrderLocation.address']);
            $resources->whereHas('tranOrderLocation', function ($q) use ($order) {
                $q->where('delivery_order_id', $order['id']);
            });
            $location_id = null;
            foreach ($resources->get()->toArray() as $resource) {
                if ($location_id !== $resource['fleet_tran_order_location_id']) {
                    $location_id = $resource['fleet_tran_order_location_id'];
                    $customer = Customer::where('partner_id', $resource['tran_order_location']['address']['parent_id'] ?? null)->first();
                    $saleman = Employee::where('id', $customer->salesman_id ?? null)->first();
                    $date = (new Carbon($order['start_date']))->format('d/m/Y');
                    $message = "Yêu cầu vận chuyển " . $order['code'] . ' - ' . $date . ' - ' . ' tại ' . $resource['tran_order_location']['address']['name'] . ' đã được điều phối.';
                    $content = $message;
                    $content .= PHP_EOL . $resource['tran_order_location']['address']['name'];
                    $notification = FleetNotification::create([
                        "tran_order_id" => $order['id'],
                        "user_id" => $userId,
                        'title' => $tile,
                        "message" => $message,
                        "content" => $content,
                        "tran_order_location_id" => $resource['fleet_tran_order_location_id'],
                        "time_notification" => Carbon::now(),
                    ]);
                    $fields = [];
                    $fields['include_external_user_ids'] = ["$userId"];
                    $fields['data'] = $notification;
                    $fields['contents']['en'] = $notification['message'];
                    $helper->sendNotification('web', $fields);
                    $helper->sendNotification('business', $fields);
                    if ($saleman) {
                        $saleman = User::where('partner_id', $saleman->partner_id ?? null)->first();
                        $notification = FleetNotification::create([
                            "tran_order_id" => $order['id'],
                            "user_id" => $saleman->id,
                            'title' => $tile,
                            "message" => $message,
                            "content" => $content,
                            "tran_order_location_id" => $resource['fleet_tran_order_location_id'],
                            "time_notification" => Carbon::now(),
                        ]);
                        $fields = [];
                        $fields['include_external_user_ids'] = ["$saleman->id"];
                        $fields['data'] = $notification;
                        $fields['contents']['en'] = $notification['message'];
                        $fields['include_external_user_ids'] = ["$saleman->id"];
                        $helper->sendNotification('web', $fields);
                    }
                }
            }
        }
    }
}
