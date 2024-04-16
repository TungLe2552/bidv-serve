<?php

namespace App\Services\Notification;

use App\Helpers\OneSignalHelper;
use App\Models\Auth\User;
use App\Models\Logistic\FleetNotification;
use Carbon\Carbon;

class NotificationDieuPhoiServices
{
    public function send($info, string $notification_code, $option = [])
    {
        $message = $info['message'];
        $type_notify = 'web';
        $helper = new OneSignalHelper();
        $user_ids = [];
        if (empty($info['time_notification'])) {
            $info['time_notification'] = Carbon::now();
        }
        $info['created_at'] = Carbon::now();
        $info['updated_at'] = Carbon::now();

        $is_dieu_phoi_cong_nhan = $option['cong_nhan']['send'] ?? true;
        $is_dieu_phoi_xe = $option['phuong_tien']['send'] ?? true;
        if ($is_dieu_phoi_cong_nhan) {
            $users = User::whereHas('groups.notificationTypes', function ($query) use ($notification_code) {
                $query->where('type', 'dieu-phoi-cong-nhan');
                $query->where('code', $notification_code);
            })->active()->get();
            foreach ($users as $key => $user) {
                $user_id = $user->getKey();
                $user_ids[] = $user_id;
                $items_insert[] = array_merge($info, [
                    'user_id' => $user_id,
                    'type' => 'dieu-phoi-cong-nhan'
                ], $option['cong_nhan']['info'] ?? []);
            }
        }
        if ($is_dieu_phoi_xe) {
            $users = User::whereHas('groups.notificationTypes', function ($query) use ($notification_code) {
                $query->where('type', 'dieu-phoi-xe');
                $query->where('code', $notification_code);
            })->active()->get();
            foreach ($users as $key => $user) {
                $user_id = $user->getKey();
                $user_ids[] = $user_id;
                $items_insert[] = array_merge($info, [
                    'user_id' => $user_id,
                    'type' => 'dieu-phoi-xe'
                ], $option['phuong_tien']['info'] ?? []);
            }
        }
        if (count($user_ids) > 0) {
            FleetNotification::insert($items_insert);
            $fields = [];
            $fields['include_external_user_ids'] = $user_ids;
            $fields['data'] = $info;
            $fields['contents']['en'] = $message;
            $helper->sendNotification($type_notify, $fields);
        }
    }
}
