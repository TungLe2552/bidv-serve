<?php

namespace App\Services\YeuCau;

use App\Constants\ResourceTypeGroupConstant;
use App\Helpers\Dynamic\TableHelper;
use App\Models\Logistic\FleetReqDetailResource;
use App\Models\Logistic\FleetTranOrderLocation;
use App\Models\Logistic\FleetTranOrderResource;
use App\Models\System\ResourceType;
use DB;

class RequestDetailResourceService
{
    public function getQueryResource(FleetReqDetailResource $detail, $group_type)
    {
        return DB::query()->fromSub(
            function ($query) use ($detail, $group_type) {
                $detail->load(['resourceType', 'location.transportationRequest']);
                $transportation_request = $detail['location']['transportationRequest'];
                $table = TableHelper::getTable($detail->resourceType->model_id);
                $query_model = TableHelper::getQuery($table);
                $table_name = $query_model->getModel()->getTable();
                $query->from($table_name);
                $query->join('hr_work_schedule_lines', function ($join) use ($detail, $table_name, $transportation_request) {
                    $join->where('hr_work_schedule_lines.resource_type_id', $detail->resource_type_id);
                    $join->where('hr_work_schedule_lines.date', $transportation_request->start_date);
                    $join->where('hr_work_schedule_lines.shift_id', $transportation_request->shift_id);
                    $join->on('hr_work_schedule_lines.resource_id', $table_name . '.id');
                });
                $query->join('hr_work_schedules', function ($join) {
                    $join->whereIn('hr_work_schedules.state',  ['2-Đang áp dụng']);
                    $join->on('hr_work_schedules.id', 'hr_work_schedule_lines.schedule_id');
                });
                if ($group_type === 'worker') {
                    $query->leftjoin('fleet_group_worker_to_request', function ($join) use ($detail, $table_name, $transportation_request) {
                        $join->on('fleet_group_worker_to_request.department_id', $table_name . '.department_id');
                    });
                    $query->select([
                        $table_name . ".*",
                        DB::raw('hr_work_schedule_lines.state as state_schedule_name'),
                        DB::raw('hr_work_schedule_lines.id as state_schedule_id'),
                        DB::raw("CASE
                            WHEN hr_work_schedule_lines.state= '1-Sẵn sàng' THEN 'primary'
                            WHEN hr_work_schedule_lines.state= '2-Trùng lịch' THEN 'warning'
                            WHEN hr_work_schedule_lines.state= '3-Nghỉ' THEN 'error'
                        END as state_schedule_color"),
                        DB::raw('fleet_group_worker_to_request.priority as worker_priority'),
                    ]);
                }else{
                    $query->select([
                        $table_name . ".*",
                        DB::raw('hr_work_schedule_lines.state as state_schedule_name'),
                        DB::raw('hr_work_schedule_lines.id as state_schedule_id'),
                        DB::raw("CASE
                            WHEN hr_work_schedule_lines.state= '1-Sẵn sàng' THEN 'primary'
                            WHEN hr_work_schedule_lines.state= '2-Trùng lịch' THEN 'warning'
                            WHEN hr_work_schedule_lines.state= '3-Nghỉ' THEN 'error'
                        END as state_schedule_color")
                    ]);
                }
            },
            'tai_nguyens'
        );
    }
    public function getQueryOrderLocation(FleetTranOrderLocation $location, $group, $cb = null)
    {
        return DB::query()->fromSub(function ($query) use ($location, $group, $cb) {
            $query->fromSub(
                function ($query) use ($location, $group, $cb) {
                    $location->load(['transportOrder.request']);
                    $resource_types = ResourceType::where('group', $group)->active()->get();
                    $transportation_request = $location['transportOrder']['request'];
                    $resource_type = $resource_types[0];
                    $table = TableHelper::getTable($resource_type->model_id);
                    $query_model = TableHelper::getQuery($table);
                    $table_name = $query_model->getModel()->getTable();
                    $query->from($table_name);
                    $query->join('hr_work_schedule_lines', function ($join) use ($resource_type, $table_name, $transportation_request) {
                        $join->where('hr_work_schedule_lines.resource_type_id', $resource_type->id);
                        $join->where('hr_work_schedule_lines.date', $transportation_request->start_date);
                        $join->where('hr_work_schedule_lines.shift_id', $transportation_request->shift_id);
                        $join->on('hr_work_schedule_lines.resource_id', $table_name . '.id');
                    });
                    $query->join('hr_work_schedules', function ($join) {
                        $join->whereIn('hr_work_schedules.state',  ['2-Đang áp dụng']);
                        $join->on('hr_work_schedules.id', 'hr_work_schedule_lines.schedule_id');
                    });
                    $query->select([
                        $table_name . ".*",
                        'hr_work_schedule_lines.resource_id',
                        DB::raw('hr_work_schedule_lines.state as state_schedule_name'),
                        DB::raw('hr_work_schedule_lines.notes as state_schedule_notes'),
                        DB::raw('hr_work_schedule_lines.id as state_schedule_id'),
                        DB::raw('hr_work_schedule_lines.number_work_assigned as state_schedule_number_work_assigned'),
                        DB::raw("CASE
                                WHEN hr_work_schedule_lines.state= '1-Sẵn sàng' THEN 'primary'
                                WHEN hr_work_schedule_lines.state= '2-Trùng lịch' THEN 'warning'
                                WHEN hr_work_schedule_lines.state= '3-Nghỉ' THEN 'error'
                            END as state_schedule_color")
                    ]);
                    if (isset($cb)) {
                        $cb($query);
                    }
                    for ($i = 1; $i < count($resource_types); $i++) {
                        $resource_type = $resource_types[$i];
                        $table = TableHelper::getTable($resource_type->model_id);
                        $query_model = TableHelper::getQuery($table);
                        $table_name = $query_model->getModel()->getTable();
                        $query_2 = DB::query();
                        $query_2->from($table_name);
                        $query_2->join('hr_work_schedule_lines', function ($join) use ($resource_type, $table_name, $transportation_request) {
                            $join->where('hr_work_schedule_lines.resource_type_id', $resource_type->id);
                            $join->where('hr_work_schedule_lines.date', $transportation_request->start_date);
                            $join->where('hr_work_schedule_lines.shift_id', $transportation_request->shift_id);
                            $join->on('hr_work_schedule_lines.resource_id', $table_name . '.id');
                        });
                        $query_2->select([
                            $table_name . ".*",
                            'hr_work_schedule_lines.resource_id',
                            DB::raw('hr_work_schedule_lines.state as state_schedule_name'),
                            DB::raw('hr_work_schedule_lines.notes as state_schedule_notes'),
                            DB::raw('hr_work_schedule_lines.id as state_schedule_id'),
                            DB::raw('hr_work_schedule_lines.number_work_assigned as state_schedule_number_work_assigned'),
                            DB::raw("CASE
                                WHEN hr_work_schedule_lines.state= '1-Sẵn sàng' THEN 'primary'
                                WHEN hr_work_schedule_lines.state= '2-Trùng lịch' THEN 'warning'
                                WHEN hr_work_schedule_lines.state= '3-Nghỉ' THEN 'error'
                            END as state_schedule_color")
                        ]);
                        if (isset($cb)) {
                            $cb($query_2);
                        }
                        $query->union($query_2);
                    }
                },
                'tai_nguyens'
            );
            $query->distinct("resource_id");
        }, 'tai_nguyens');
    }
    public function getInfoForResource(FleetTranOrderResource $resource)
    {
        $resource_type = $resource->resourceType;
        $resource_id = $resource->resource_id;
        $type_notify = '';
        switch ($resource_type->group) {
            case ResourceTypeGroupConstant::PHUONG_TIEN:
                $type_notify = 'driver';
                $partner_id = $resource->partner_id;
                if (empty($partner_id)) {
                    $table = TableHelper::getTable($resource_type->model_id); // table vehicles hoặc employees
                    $query = TableHelper::getQuery($table);
                    $tmp = $query->find($resource_id);
                    if (isset($tmp)) {
                        $employee_id = $tmp->driver_id;
                        $user = DB::table('materialized_employee_with_user')->where('employe_id', $employee_id)->select('user_id', 'partner_id')->first();
                    }
                } else {
                    $user = DB::table('materialized_employee_with_user')->where('partner_id', $partner_id)->select('user_id', 'partner_id')->first();
                }
                break;
            case ResourceTypeGroupConstant::NHAN_SU:
                $type_notify = 'worker';
                $employee_id = $resource_id;
                $user = DB::table('materialized_employee_with_user')->where('employe_id', $employee_id)->select('user_id', 'partner_id')->first();
                break;
        }
        if (empty($user)) {
            return;
        }
        $user_id = $user->user_id;
        return ['type_notify' => $type_notify, 'user_id' => $user_id, 'partner_id' => $user->partner_id];
    }
}
