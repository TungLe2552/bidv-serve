<?php

namespace App\Console\Commands;

use App\Models\BA\Contract\Contract;
use App\Models\BA\Contract\ContractAppendix;
use App\Models\Res\ResContract;
use App\Models\System\ScheduleModel;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutomaticContractRenewalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:gia-han-hop-dong-tu-dong';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Câu lệnh dùng để gia hạn hợp đồng, phụ lục hợp đồng tự động';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $param = 7;
        $command = ScheduleModel::where('command', $this->signature)->first();
        if (!empty($command)) {
            $params = $command->params;
            if (!empty($params) && !empty($params['"day"'])) {
                $param = $params['"day"'];
            }
            $now = Carbon::now();
            $res_contracts = ResContract::whereHas('baContract', function ($query) {
                $query->whereHas('overStatuses', function ($query) {
                    $query->where('code', 'Active');
                });
            })->whereDate('end_date', '>=', $now)
                ->whereDate('end_date', '<=', $now->copy()->addDays($param))
                ->with(['baContract'])
                ->get()->pluck('id');
            if (count($res_contracts) === 0) {
                $this->info('* Không tìm thấy hợp đồng nào cần ra hạn tự động;');
            } else {
                foreach ($res_contracts as $key => $value) {
                    $contract = ResContract::find($value)->load(['baContract']);
                    $end_date = Carbon::createFromDate($contract['end_date']);
                    if (empty($contract['baContract']['renewal_period']) && ($contract['baContract']['renewal_date'] == $now->copy()->format('Y-m-d'))) {
                        $contract->update([
                            'end_date' => $end_date->copy()->addMonths(12)
                        ]);
                        $this->info('* Đã gia hạn thành công hợp đồng' . $contract['contract_reference'] . ';');
                        $this->checkContractAppendix($contract, 12);
                    } else
                    if (!empty($contract['baContract']['renewal_period']) && ($contract['baContract']['renewal_date'] == $now->copy()->format('Y-m-d'))) {
                        $contract->update([
                            'end_date' => $end_date->copy()->addMonths($contract['baContract']['renewal_period'])
                        ]);
                        $this->info('* Đã gia hạn thành công hợp đồng' . $contract['contract_reference'] . ';');
                        $this->checkContractAppendix($contract, $contract['baContract']['renewal_period']);
                    } else if (!empty($contract['baContract']['renewal_period']) && ($contract['baContract']['renewal_date'] !== $now->copy()->format('Y-m-d'))) {
                        $contract->update([
                            'end_date' => $end_date->copy()->addMonths($contract['baContract']['renewal_period'])
                        ]);
                        $this->info('* Đã gia hạn thành công hợp đồng' . $contract['contract_reference'] . ';');
                        $this->checkContractAppendix($contract, $contract['baContract']['renewal_period']);
                    } else {
                        $this->info('* Hợp đồng ' . $contract['contract_reference'] . ' không đủ điều kiện ra hạn tự động;');
                    }
                }
            }
        }
    }

    public function checkContractAppendix($contract, $renewal_period)
    {
        $contract_appendixs =  ContractAppendix::where('contract_id', $contract['baContract']['id'])->get();
        if (!empty($contract_appendixs)) {
            foreach ($contract_appendixs as $key => $value) {
                $contract_appendix = ContractAppendix::find($value->id);
                $end_date = Carbon::createFromDate($contract_appendix['end_date']);
                $contract_appendix->update([
                    'end_date' => $end_date->copy()->addMonths($renewal_period)
                ]);
                $this->info('Đã ra hạn thành công phụ lục ' . $contract_appendix['appendix_number'] . 'của hợp đồng ' . $contract['contract_reference'] . ';');
            }
        } else {
            $this->info('Không tìm thấy phụ lục hợp đồng của hợp đồng ' . $contract['contract_reference'] . ' cần gia hạn;');
        }
    }
}
