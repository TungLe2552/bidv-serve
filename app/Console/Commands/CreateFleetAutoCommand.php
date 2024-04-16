<?php

namespace App\Console\Commands;

use App\Models\Logistic\FleetReqDetailLocation;
use App\Models\Logistic\FleetReqDetailResource;
use App\Models\Logistic\FleetReqResourceCondition;
use App\Models\Logistic\FleetReqStatus;
use App\Models\Logistic\FleetReqType;
use App\Models\Logistic\FleetTransportationRequest;
use App\Models\Logistic\FleetTransportationRequestAutomatically;
use App\Models\Res\Partner;
use App\Models\System\ScheduleModel;
use App\Repositories\BaseRepository;
use DB;
use Gate;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;

class CreateFleetAutoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sinh-tu-dong-yeu-cau-dat-xe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Câu lệnh dùng cho việc tạo yêu cầu đặt xe từ lịch xe tự động';
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     */

    public function handle()
    {

        $fleets_auto = FleetTransportationRequestAutomatically::where('active', true)->get();
        if ($fleets_auto->isEmpty()) {
            $this->info('Không có lịch xe tự động thỏa mãn;');
        } else {
            $today = Carbon::now();
            foreach ($fleets_auto as $key => $value) {
                $this->processFleetRequest($key, $value, $today);
            }
        }
        return 0;
    }

    private function processFleetRequest($key, $value, $today)
    {
        $this->info('* Duyệt LX số: ' . ($key + 1) . '; B1A- Xét LXTĐ có ID: ' . $value->id . ' (d/s thứ: ' . $value->date_list_in_week . ', d/s ngày: ' . $value->date_list_in_month . ');');
        if (!empty($value['date_list_in_month'])) {
            // Xử lý trường hợp có ngày
            $this->processByMonth($value, $today);
        } else {
            // Xử lý trường hợp không có ngày
            $this->processWithoutMonth($value, $today);
        }
    }
    private function processByMonth($value, $today)
    {
        $dayOfMonth = explode(', ', $value['date_list_in_month']);
        if (in_array($today->day, $dayOfMonth)) {
            $result = $this->createFleetTranReq($value, $today);
            $this->logResult($result, 'B2:Có giá trị ngày thỏa mãn, tạo mới thành công YCĐ');
        }
    }
    private function processWithoutMonth($value, $today)
    {
        if (!empty($value['date_list_in_week'])) {
            // Xử lý trường hợp không có ngày, có thứ
            $dayOfWeek = explode(', ', $value['date_list_in_week']);
            $dayOfWeekTranslation = Lang::get('date.weekdays.' . $today->englishDayOfWeek);
            if (in_array($dayOfWeekTranslation, $dayOfWeek)) {
                $result = $this->createFleetTranReq($value, $today);
                $this->logResult($result, 'B3:Có giá trị thứ thỏa mãn, tạo mới thành công YCĐ');
            }
        }
    }
    private function logResult($result, $message)
    {
        if (!empty($result)) {
            $this->info("$message: $result->id - $result->name - $result->created_at;");
        } else {
            $this->info('Có lỗi xảy ra xem file log;');
        }
    }
    public function createFleetTranReq($value, $today)
    {
        $attributes = $value->toArray();
        unset($attributes['date_list_in_week']);
        unset($attributes['date_list_in_month']);
        $attributes['start_date'] = $today->format('Y-m-d');

        $repository = new BaseRepository(FleetTransportationRequest::class);
        $cb = function ($model, $attributes) {
            $dia_chis = $attributes['dia_chis'];
            $companies = [];
            $default_name = 'Yêu cầu vận chuyển tới ';
            $req_name = null;

            foreach ($dia_chis as $dia_chi) {
                $destination = Partner::findOrFail($dia_chi['address_id']);
                $customer = $destination->parent;
                if (!empty($customer)) {
                    if (count($companies) === 0) {
                        array_push($companies, $customer->getKey());

                        $req_name = $default_name . $customer->short_name;

                        continue;
                    };

                    if (!in_array($customer->getKey(), $companies)) {
                        array_push($companies, $customer->getKey());

                        $req_name = $req_name . ', ' . $customer->short_name;
                    }
                }
            }

            $type_request = FleetReqType::findOrFail($model->type_id);

            if ($req_name !== $default_name) {
                $req_name = $req_name . ' - ' . $type_request->name;
            } else {
                $req_name = $type_request->name;
            };

            $model->name = $req_name;
            $model->generate_automatically = true;
            $model->status_id = FleetReqStatus::isActive()->defaultOrder()->firstOrFail()->getKey();
            $model->created_by_id = $attributes['users']['id'];
            $model->updated_by_id = null;
        };

        $cb_after = function ($model, $attributes) {

            $dia_chis = $attributes['dia_chis'];
            foreach ($dia_chis as $dia_chi) {
                $resources = $dia_chi['resources'];
                unset($dia_chi['resources']);
                $dia_chi['destination_id'] = $dia_chi['address_id'];
                unset($dia_chi['address_id']);
                $detail = FleetReqDetailLocation::create(array_merge(['tr_req_id' => $model->getKey()], $dia_chi));

                foreach ($resources as $resource) {
                    $detail_location = FleetReqDetailResource::create([
                        'tr_req_detail_location_id' => $detail->getKey(),
                        'resource_type_id' => $resource['type_id'],
                        'number_of_resources_required' => $resource['count'],
                    ]);
                    foreach ($resource['conditions'] as $item) {
                        if (!empty($item['value'])) {
                            FleetReqResourceCondition::create([
                                'tr_req_detail_id' => $detail_location->getKey(),
                                'condition_id' => $item['condition_id'],
                                'value' => $item['value'],
                            ]);
                        }
                    }
                }
            }
        };
        $disabled_check = true;
        if (!$disabled_check) {
            Gate::authorize('create-model-feature', $repository->getModel());
        }
        $result = $repository->create($attributes, $cb, $cb_after);
        return $result;
    }
}
