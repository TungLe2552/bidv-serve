<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Helpers\Dynamic\TableHelper;
use App\Models\Auth\User;
use App\Models\Base\IrModel;
use Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use Illuminate\Auth\Access\Response;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('read-model-feature', function (User $user, string $table) {
            if (!config('app.check_user_permission', false)) {
                return  Response::allow();
            }
            $model = $this->getTable($table);
            $access = $model->getAccess();
            return $access['perm_read'] ? Response::allow()
                : Response::denyWithStatus(400, 'Bạn không có quyền truy cập');
        });
        Gate::define('create-model-feature', function (User $user, string $table) {
            if (!config('app.check_user_permission', false)) {
                return  Response::allow();
            }
            $model = $this->getTable($table);
            $access = $model->getAccess();
            return $access['perm_create'] ? Response::allow()
                : Response::denyWithStatus(400, 'Bạn không có quyền truy cập');
        });
        Gate::define('update-model-feature', function (User $user, string $table) {
            if (!config('app.check_user_permission', false)) {
                return  Response::allow();
            }
            $model = $this->getTable($table);
            $access = $model->getAccess();
            return $access['perm_write'] ? Response::allow()
                : Response::denyWithStatus(400, 'Bạn không có quyền truy cập');
        });
        Gate::define('delete-model-feature', function (User $user, string $table) {
            if (!config('app.check_user_permission', false)) {
                return  Response::allow();
            }
            $model = $this->getTable($table);
            $access = $model->getAccess();
            return $access['perm_unlink'] ? Response::allow()
                : Response::denyWithStatus(400, 'Bạn không có quyền truy cập');
        });
    }
    private function getTableName(string $table)
    {
        if (class_exists($table)) {
            return (new $table)->getTable();
        }
        if (str_contains($table, '.')) {
            [$table, $type] = explode(".", $table);
        }
        return $table;
    }
    private function getTable(string $table)
    {
        $table_name = $this->getTableName($table);
        $model = TableHelper::getTableByCode($table_name, false);
        if (empty($model)) {
            abort(404, 'Bảng ' . $table_name . ' chưa được khai báo');
        }
        return $model;
    }
}
