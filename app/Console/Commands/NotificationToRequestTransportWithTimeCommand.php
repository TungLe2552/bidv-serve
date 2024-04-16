<?php

namespace App\Console\Commands;

use App\Models\Logistic\FleetNotification;
use App\Models\Logistic\FleetTransportationRequest;
use App\Models\System\ScheduleModel;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotificationToRequestTransportWithTimeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:thong-bao-yeu-cau-chua-duoc-dieu-phoi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Câu lệnh này dùng để thống báo các yêu cầu chưa được điều phối theo thời gian cấu hình';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $param = 1;
        $paramsCommand = ScheduleModel::where('command', 'app:thong-bao-yeu-cau-chua-duoc-dieu-phoi')->first();
        if (!empty($paramsCommand)) {
            $params = $paramsCommand->params;
            if (!empty($params) && !empty($params['"time"'])) {
                $param = $params['"time"'];
            }
        }

        $today = Carbon::today();
        $now = Carbon::now();
        $oneHourAgo = $now->copy()->addHour($param);


        $transportRequest = FleetTransportationRequest::whereDate('created_at', $today)
            ->whereHas('status', function ($query) {
                $query->where('code', 1);
            })
            ->whereHas('details', function ($query) use ($oneHourAgo, $now) {
                $query->whereTime('latest_pickup_time', '<=', $oneHourAgo->format('H:i:s'))
                    ->whereTime('latest_pickup_time', '>=', $now->format('H:i:s'));
            })
            ->with('details')
            ->get();

        $this->info('* Có ' . count($transportRequest) . ' yêu cầu sắp đến giờ đến KH mà chưa được điều phối ;');
        foreach ($transportRequest as $key2 => $value) {
            $this->info('* Kiếm tra login của người tạo YCVC tải số ' . ($key2 + 1) . ' để TB;');
            if (!empty($value['created_by_id'])) {
                $this->info('Thông báo tới người tạo YCVC: ' . ($value->created_by_id) . ';');
                $noti = FleetNotification::create([
                    'message' => 'Yều vận chuyển ' . ($value['id']) . '-' . ($value['name']) . ' sắp đến giờ đến khách hàng "' . ($value['details'][0]['latest_pickup_time']) . '" mà chưa được điều phối',
                    'user_id' => $value->created_by_id,
                    'status' => '1-Chưa đọc',
                    'time_notification' => Carbon::now(),
                    'res_model' => 'fleet_transportation_requests',
                    'res_id' => $value->id,
                ]);
                if (!empty($noti)) {
                    $this->info('Thông báo thành công;');
                } else {
                    $this->info('Thông báo thất bại xem file log;');
                }
            }
        }
    }
}
