<?php

namespace App\Traits;

use App\Models\System\Group;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

trait UserHasApplicationTrait
{
    protected $is_admin = null;
    public $is_use_table = true;
    protected $app_check = [];
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'res_user_groups', 'user_id', 'group_id');
    }

    public function canAccessApplication($application)
    {
        if (empty($this->app_check[$application]) || is_null($this->app_check[$application])) {
            $this->app_check[$application] = $this->groups()->whereHas('groupApplications', function ($query) use ($application) {
                $query->where('code', $application);
            })->exists();
        }
        return $this->app_check[$application];
    }
    public function isAdministrator()
    {
        if (is_null($this->is_admin)) {
            $this->is_admin = $this->groups->where('name', 'Administrator')->isNotEmpty();
        }
        return $this->is_admin;;
    }
}
