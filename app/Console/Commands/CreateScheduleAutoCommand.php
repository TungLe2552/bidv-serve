<?php

namespace App\Console\Commands;

use App\Constants\ResourceTypeGroupConstant;
use App\Models\HR\WorkSchedule;
use App\Models\HR\WorkScheduleLine;
use App\Models\Logistic\FleetShift;
use App\Models\System\ResourceType;
use App\Models\System\ScheduleModel;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Helpers\Dynamic\TableHelper;
use Illuminate\Console\Command;

class CreateScheduleAutoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:tao-lich-lam-viec-theo-thang';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Câu lệnh dùng để tạo lịch làm việc của công nhân và xe theo tháng vào ngày 25 hàng tháng';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $schedule_command = ScheduleModel::where('command', 'app:tao-lich-lam-viec-theo-thang')->first();
        if (!empty($schedule_command)) {
            $now = Carbon::now();
            $time_next_month = $now->copy()->addMonth();
            $start_of_next_month = $time_next_month->copy()->startOfMonth()->format('Y-m-d');
            $end_of_next_month = $time_next_month->copy()->endOfMonth()->format('Y-m-d');
            $complete_time = $time_next_month->copy()->addMonth();
            $complete_date = $complete_time->copy()->startOfMonth()->format('Y-m-d');
            $month = $time_next_month->copy()->format('m');
            $year = $time_next_month->copy()->format('Y');
            $before_create = WorkSchedule::whereMonth('start_day', $month)->whereYear('start_day', $year)->where('state', '2-Đang áp dụng')->get();
            if($before_create->isNotEmpty()) {
                foreach ($before_create as $item) {
                    $item->update(['state' => '4-Hủy']);
                }
            }
            $schedule = WorkSchedule::create([
                'name' => 'Lịch làm việc tự động tháng ' . $time_next_month->format('m') . '/' . $time_next_month->format('Y'),
                'start_day' => $start_of_next_month,
                'end_day' => $end_of_next_month,
                'state' => '2-Đang áp dụng',
                'completed_at' => $complete_date
            ]);
            if (!empty($schedule)) {
                $this->info('* Thêm mới thành công ' . $schedule->name);
                $period = CarbonPeriod::create($start_of_next_month, $end_of_next_month);
                $shifts = FleetShift::get();
                $resource_types = ResourceType::whereIn('group', [ResourceTypeGroupConstant::NHAN_SU, ResourceTypeGroupConstant::PHUONG_TIEN])->where('active', true)->get();
                if (empty($shifts)) {
                    $this->info("* Không tìm thấy ca làm việc nào");
                }
                if (empty($resource_types)) {
                    $this->info("* Không tìm thấy loại nguồn lực nào thỏa mãn");
                }
                foreach ($period as $date) {
                    foreach ($resource_types as $resource_type)
                {
                    $query = TableHelper::getQuery($resource_type->model)->select('id');
                    $items = $query->get();
                    foreach ($shifts as $shift)
                    {
                        foreach ($items as $item)
                        {
                            $schedule_lines[] = [
                                'schedule_id' => $schedule->id,
                                'resource_id' => $item->id,
                                'resource_type_id' => $resource_type->id,
                                'shift_id' => $shift->id,
                                'date' => $date,
                                'state' => '1-Sẵn sàng'
                            ];
                        }
                    }
                }
                }
                $chunks = array_chunk($schedule_lines, 1000);
                foreach ($chunks as $chunk) {
                    WorkScheduleLine::insert($chunk);
                }
                $this->info('* Đã thêm mới thành công ' . count($schedule_lines) . ' lịch làm việc của công nhân và xe từ ngày ' . $start_of_next_month . ' đến ' . $end_of_next_month);
            } else {
                $this->info("* Thêm mới lịch làm việc tự động thất bại");
            }
        }
    }
}
