<?php

namespace App\Services\Employee;

use App\Constants\UserStatus;
use App\Models\Auth\User;
use App\Models\HR\Employee;
use App\Models\Res\Partner;
use App\Repositories\BaseRepository;
use App\Traits\AddInformation;
use DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeService
{
    use AddInformation;
    protected $employee_repository;
    protected $partner_repository;
    protected $user_repository;
    protected $bank_repository;
    protected $user_extra_create;
    public function __construct()
    {
        $this->employee_repository = new BaseRepository(Employee::class, [Employee::LOG_NAME]);
        $this->partner_repository = new BaseRepository(Partner::class, [Partner::LOG_NAME]);
        $this->user_repository = new BaseRepository(User::class);
        $this->user_extra_create = function ($model, $attributes) {
            $model->password = $attributes['password'];
        };
    }
    public function createEmployee(array $data)
    {
        DB::beginTransaction();
        try {
            $partner_extra_data = [
                "is_employee" => true,
                "ref" => $data['identification_id'],
                'email' =>  $data['work_email'],
                'mobile' => $data['mobile_phone'],
                'phone' => $data['work_phone']
            ];
            $partner_data = array_merge($data, $partner_extra_data);
            unset($partner_data['parent_id']);
            $partner = $this->partner_repository->create($partner_data);
            $employee = $this->employee_repository->create(array_merge($data, ["partner_id" => $partner->id]));

            $additional_informations = $data['additional_informations'];
            if (!empty($additional_informations)) {
                $this->setInformation($additional_informations, $employee->id);
            }

            DB::commit();
            return $employee;
        } catch (\Exception $e) {
            DB::rollback();
            abort(500, 'Thêm thông tin nhân viên không thành công');
            throw $e;
        }
    }
    public function updateEmployee($id, array $data)
    {
        DB::beginTransaction();

        try {
            $employee = $this->employee_repository->update($id, array_merge($data));
            unset($data["parent_id"]);
            $partner_extra_data = [
                "ref" => $data['identification_id'],
                'email' =>  $data['work_email'],
                'mobile' => $data['mobile_phone'],
                'phone' => $data['work_phone']
            ];
            $partner = $this->partner_repository->update($employee->partner_id, array_merge($data, $partner_extra_data));

            $additional_informations = $data['additional_informations'];
            if (!empty($additional_informations)) {
                $this->setInformation($additional_informations, $id);
            }

            DB::commit();
            return $partner;
        } catch (\Exception $e) {
            DB::rollback();
            abort(500, 'Chỉnh sửa thông tin nhân viên không thành công');
            throw $e;
        }
    }

    public function deleteEmployee($id)
    {
        DB::beginTransaction();
        try {
            $partner_id = $this->employee_repository->getModel()::where('id', $id)->pluck('partner_id')->first();
            $this->employee_repository->delete($id);
            if ($partner_id) {
                $this->partner_repository->delete($partner_id);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            abort(500, 'Xóa nhân viên không thành công');
            throw $e;
        }
    }
}
