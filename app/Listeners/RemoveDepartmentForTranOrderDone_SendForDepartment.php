<?php

namespace App\Listeners;

use App\Constants\TypeNotification;
use App\Events\RemoveDepartmentForTranOrderDone;
use App\Helpers\Notification\TransportationRequestNotificationHelper;
use App\Helpers\OneSignalHelper;
use App\Models\Logistic\FleetNotification;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RemoveDepartmentForTranOrderDone_SendForDepartment
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
    public function handle(RemoveDepartmentForTranOrderDone $event): void
    {
        $order = $event->order;
        $department = $event->department;
        $location = $event->location;
        if (!$department->notification_sent) {
            return;
        }
        $extra_department = DB::table('materialized_user_for_employee_department')->where('department_id', $department->department_id)->first();

        $helper = new OneSignalHelper();
        $tran_request = $order['request'] ?? null;
        $user_id = $extra_department->user_id ?? null;
        $message = "Nhiệm vụ " . TransportationRequestNotificationHelper::getTitle($tran_request) . " tại " . $location->address->name . " đã bị hủy";
        $content = $message;
        $notification = FleetNotification::create([
            "tran_order_id" => $order['id'],
            "user_id" => $user_id,
            'title' => 'Hủy điều phối',
            'content' => $content,
            "tran_order_location_id" => $location['id'],
            "message" => $message,
            "time_notification" => Carbon::now(),
        ]);
        $fields = [];
        $fields['include_external_user_ids'] = ["$user_id"];
        $fields['data'] = $notification;
        $fields['contents']['en'] = $notification['message'];
        $helper->sendNotification(TypeNotification::WORKER, $fields);
    }
}
