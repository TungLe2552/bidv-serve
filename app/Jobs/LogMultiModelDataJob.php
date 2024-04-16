<?php

namespace App\Jobs;

use App\Enum\LogActionMethodEnum;
use App\Models\Auth\User;
use App\Repositories\LogRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogMultiModelDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $datas;
    protected $user;
    public function __construct(array $datas, User $user)
    {
        $this->datas = $datas;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $log = new LogRepository;
        $log->setUser($this->user);
        foreach ($this->datas as $value) {
            $old_data = $value['old'];
            $new_data = $value['new'];
            print("old:" . ($old_data['id'] || 'new') . " - old:" . ($new_data['id'] || 'new') . " \n");
            print("old:" . json_encode($old_data));
            print("new:" . json_encode($new_data));

            $method = !empty($new_data->getKey()) ? LogActionMethodEnum::UPDATED->value : LogActionMethodEnum::CREATED->value;

            $log->checkLog($new_data->getTable(), $method, function ($model, $method) use ($new_data, $old_data, $log) {
                $log->logModel($model, $method, $new_data, $old_data);
            });
        }
        $log->setUser(null);
    }
}
