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

class LogModelDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $old_data;
    protected $new_data;
    protected $user;
    public function __construct(Model $old_data, Model $new_data, User $user)
    {
        $this->old_data = $old_data;
        $this->new_data = $new_data;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $old_data = $this->old_data;
        $new_data = $this->new_data;
        $log = LogRepository::getInstance();

        $method = !empty($new_data->getKey()) ? LogActionMethodEnum::UPDATED->value : LogActionMethodEnum::CREATED->value;
        $log->setUser($this->user);
        $log->checkLog($new_data->getTable(), $method, function ($model, $method) use ($new_data, $old_data, $log) {
            $log->logModel($model, $method, $new_data, $old_data);
        });
        $log->setUser(null);
    }
}
