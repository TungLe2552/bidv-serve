<?php

namespace App\Listeners;

use App\Constants\ResourceTypeGroupConstant;
use App\Events\CreateTransportationOrderDone;
use App\Helpers\Notification\TransportationRequestNotificationHelper;
use App\Helpers\OneSignalHelper;
use App\Models\Auth\User;
use App\Models\Logistic\FleetNotification;
use App\Models\System\NotificationType;
use App\Services\Notification\NotificationDieuPhoiServices;
use Carbon\Carbon;

class CreateTransportationOrderDone_SendForDieuPhoi
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
    public function handle(CreateTransportationOrderDone $event): void
    {
        $service = new NotificationDieuPhoiServices();
        $tran_request = $event->tran_request;
        $message = 'Yêu cầu vận tải mới';
        $context = $message;
        $context .= PHP_EOL . TransportationRequestNotificationHelper::getContent($tran_request);
        $is_dieu_phoi_xe = $tran_request->locations->some(function ($item) {
            return $item->detailResources->some(function ($detail) {
                return $detail->resourceType->group === ResourceTypeGroupConstant::PHUONG_TIEN;
            });
        });
        $is_dieu_phoi_cong_nhan = $tran_request->locations->some(function ($item) {
            return $item->detailResources->some(function ($detail) {
                return $detail->resourceType->group === ResourceTypeGroupConstant::NHAN_SU;
            });
        });
        $service->send(
            [
                "message" => $message,
                'title' => 'Yêu cầu vận tải mới',
                'content' => $context,
                'options' => json_encode([
                    'tran_request_id' => $tran_request->getKey(),
                    'create_by_id' => $tran_request->created_by_id,
                    'request_date' => $tran_request['start_date'],
                ]),
            ],
            NotificationType::CREATE_TRANSPORTATION_ORDER,
            ['cong_nhan' => ['send' => $is_dieu_phoi_cong_nhan], 'phuong_tien' => ['send' => $is_dieu_phoi_xe]]
        );
    }
}
