<?php

namespace App\Console\Commands;

use App\Models\BA\KhieuNai\FileAttachmentTemporary;
use App\Models\System\ScheduleModel;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RemoveTemporaryUselessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:xoa-file-bang-tam';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Câu lệnh để xóa bỏ các tệp tin lưu bảng tạm để bỏ thứ không cần thiết';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $schedule = ScheduleModel::where('command', 'app:remove-temporary-useless-command')->where('status', true)->first();
        if (!empty($schedule)) {
            $params = $schedule->params;
// Ưu tiên xóa bản ghi đã được chuyển đổi
            $temporaryIsGenerated = FileAttachmentTemporary::where('is_generated', true)->get();
            $this->info('*Có ' . count($temporaryIsGenerated) . ' đã được chuyển đổi chuẩn bị xóa ;');
            if (count($temporaryIsGenerated) > 0) {
                $result = $temporaryIsGenerated->each->delete();
                if (count($result) > 0) {
                    $this->info('Xóa thành công;');
                } else {
                    $this->info('Xóa thất bại xem file log;');
                }
            }
// Dựa vào params sẽ xóa các bản ghi theo params nếu params rỗng thì sẽ các bản ghi từ 1 tháng trở về trước
            if (!empty($params) && isset($params['day'])) {
                $this->info('*Có tham số xóa kèm theo là ' . $params['day'] . ';');
                $dayCarbon = Carbon::now()->subDays($params['day']);
            } else {
                $this->info('*Không tham số xóa kèm theo mặc định xóa các bản ghi 30 ngày trước;');
                $dayCarbon = Carbon::now()->subDays(30);
            }
            $temporaryBelongsParams = FileAttachmentTemporary::where('is_generated', false)->where('created_at', '<', $dayCarbon)->get();
            $this->info('*Có ' . count($temporaryBelongsParams) . ' thỏa mãn thời gian chuẩn bị xóa ;');
            if (count($temporaryBelongsParams) > 0) {
                $result = $temporaryBelongsParams->each->delete();
                if (count($result) > 0) {
                    $this->info('Xóa thành công;');
                } else {
                    $this->info('Xóa thất bại xem file log;');
                }
            }

        } else {
            $this->info('*Không có câu lệnh thỏa mãn ;');

        }
        return 0;

    }
}
