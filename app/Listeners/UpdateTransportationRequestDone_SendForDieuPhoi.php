<?php

namespace App\Listeners;

use App\Constants\ResourceTypeGroupConstant;
use App\Events\UpdateTransportationRequestDone;
use App\Helpers\Notification\TransportationRequestNotificationHelper;
use App\Models\System\NotificationType;
use App\Services\Notification\NotificationDieuPhoiServices;

class UpdateTransportationRequestDone_SendForDieuPhoi
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
    public function handle(UpdateTransportationRequestDone $event): void
    {
        $tran_request = $event->tran_request;
        if (empty($tran_request->order)) {
            return;
        }
        $service = new NotificationDieuPhoiServices();
        $message = 'Yêu cầu vận tải được cập nhật';
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
        $context = TransportationRequestNotificationHelper::getTitle($tran_request);
        $context .= PHP_EOL . TransportationRequestNotificationHelper::getContent($tran_request);

        $service->send([
            "message" => $message,
            'title' => 'Yêu cầu vận tải được cập nhật',
            'content' => $context,
            'options' => json_encode([
                'tran_request_id' => $tran_request->getKey(),
                'create_by_id' => $tran_request->created_by_id,
                'request_date' => $tran_request['start_date'],
            ]),
        ], NotificationType::UPDATE_TRANSPORTATION_REQUEST, ['cong_nhan' => ['send' => $is_dieu_phoi_cong_nhan], 'phuong_tien' => ['send' => $is_dieu_phoi_xe,]]);
    }
}
