<?php

namespace App\Console\Commands;

use App\Models\Auth\User;
use App\Models\Logistic\FleetNotification;
use App\Models\Res\ResContract;
use App\Models\System\ScheduleModel;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotificationExpiredDateContractCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:thong-bao-hop-dong-sap-het-han';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Câu lệnh dùng để thông báo các hợp đồng sắp hết hạn có thêm thời gian tùy chỉnh';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $param = 7;
        $paramsCommand = ScheduleModel::where('command', 'app:thong-bao-hop-dong-sap-het-han')->first();
        if (!empty($paramsCommand)) {
            $params = $paramsCommand->params;
            if (!empty($params) && !empty($params['"day"'])) {
                $param = $params['"day"'];
            }
        }
        $today = Carbon::now();
        //Lấy tất cả hợp động sắp hết hạn là ngày hôm này nhỏ hơn ngày hết hạn

        $res_contracts = ResContract::whereHas('baContract', function ($query) {
            $query->whereHas('overStatuses', function ($query) {
                $query->where('code', 'Active');
            });
        })
            ->whereDate('end_date', '>=', $today)
            ->whereDate('end_date', '<=', $today->addDays($param))
            ->with(['baContract'])
            ->get();


        if (count($res_contracts) > 0) {

            $this->info('* Có ' . count($res_contracts) . ' hợp đồng chuẩn bị hết hạn ;');
            $NVKD_user_id = collect([]);
            $NVKD_user_id_Test = collect([]);
            foreach ($res_contracts as $key => $value) {


                if ($value->baContract && $value->baContract->partner && $value->baContract->partner->user) {
                    if (count($value->baContract->partner->user->pluck('id')) != 0) {
                        $NVKD_user_id->push([
                            'user_id' => $value->baContract->partner->user->pluck('id')[0],
                            'contract_reference' => $value->contract_reference,
                            'contract_id' => $value->baContract->id,
                            'contract' => $value->baContract,
                            'salesman_id' => $value->baContract->salesman_id,
                        ]);
                        $NVKD_user_id_Test->push([
                            'user_id' => $value->baContract->partner->user->pluck('id')[0],
                            'contract_reference' => $value->contract_reference,
                            'contract_id' => $value->baContract->id,
                            'contract' => $value->baContract,
                            'salesman_id' => $value->baContract->salesman_id,
                        ]);
                    }
                } else if ($value->baContract && empty($value->baContract->partner) && empty($value->baContract->partner->user)) {
                    $NVKD_user_id->push([
                        'user_id' => null,
                        'contract_reference' => $value->contract_reference,
                        'contract_id' => $value->baContract->id,
                        'salesman_id' => $value->baContract->salesman_id,
                        'contract' => $value->baContract,
                    ]);
                } else {
                    $NVKD_user_id->push([
                        'user_id' => null,
                        'contract' => null,
                    ]);
                }
            }



            $data = $NVKD_user_id->toArray();
            $result = [];
            foreach ($data as $item) {
                $userId = $item['user_id'];
                if (!isset($result[$userId])) {
                    $result[$userId] = [];
                }
                $result[$userId][] = $item;
            }

            // Chuyển mảng kết quả thành mảng tuần tự nếu bạn muốn
            $result = array_values($result);


            // dd($result);
            // dd($NVKD_user_id_Test->toArray(), $result);

            // Tính khoảng số ngày hết hạn rồi đối chiếu với tham số cài đặt, so sánh ngày hết hạn với ngày hôm nay cộng với thời gian cấu hình nếu nhỏ hơn hoặc bằng
            // tức là khoảng số ngày giữa ngày hết hạn và hôm nay nằm trong giá trị tham số cái đặt nên các hợp đồng này sẽ được thông báo là sắp hết hạn và vào cập nhật gia hạn
            // Thông báo tới NVKD của hợp đồng thỏa mãn tham số cài đặt
            // dd($NVKD_user_id_Test);

            foreach ($result as $key2 => $valueArray) {


                $this->info('* Kiếm tra login của NVKD phụ trách  ' . (count($valueArray)) . ' sắp hết hạn để TB;');


                if (count($valueArray) > 0 && !empty($valueArray[0]['user_id'])) {
                    $contractString = '';
                    $contractIdString = '';
                    foreach ($valueArray as $key => $value) {
                        $contractString .= $value['contract_reference'] . ', ';
                        $contractIdString .= $value['contract_id'] . ', ';
                    }
                    $this->info('Thông báo tới NVKD: ' . ($value['contract']->partner->id . ' - ' . $value['contract']->partner->name) . ';');
                    $noti = FleetNotification::create([
                        'message' => 'Hợp đồng số ' . $contractString . ' chuẩn bị hết hạn',
                        'user_id' => $value['user_id'],
                        'status' => '1-Chưa đọc',
                        'time_notification' => Carbon::now(),
                        'res_model' => 'ba_contracts',
                        'res_id' => $contractIdString,
                        'options' => 'salesman_id:' . $valueArray[0]['salesman_id'] . ',params:' .  $param,
                    ]);
                    if (!empty($noti)) {
                        $this->info('Thông báo thành công;');
                    } else {
                        $this->info('Thông báo thất bại xem file log;');
                    }
                } else {
                    $this->info('* Không tồn tại nhân viên phụ trách cho cách hợp đồng hết hạn');
                }
            }
        }
    }
}
