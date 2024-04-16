<?php

namespace App\Services\Customer;

use App\Models\BA\Customer;
use App\Models\Res\Area;
use App\Models\Res\District;
use App\Models\Res\Partner;
use App\Models\Res\PartnerBank;
use App\Models\Res\Province;
use App\Models\Res\ResContract;
use App\Repositories\BaseRepository;
use DB;

class CustomerService
{
    protected $customer_repository;
    protected $partner_repository;
    protected $bank_repository;
    protected $contract_repository;
    public function __construct()
    {
        $this->customer_repository = new BaseRepository(Customer::class, [Customer::LOG_NAME]);
        $this->partner_repository = new BaseRepository(Partner::class, [Partner::LOG_NAME]);
        $this->bank_repository = new BaseRepository(PartnerBank::class, [PartnerBank::LOG_NAME]);
        $this->contract_repository = new BaseRepository(ResContract::class, [ResContract::LOG_NAME]);
    }
    public function createCustomer(array $data)
    {
        DB::beginTransaction();
        try {

            $is_company = $data['is_company'];
            $contact_address = Partner::contactAdress($data);
            $partner = $this->partner_repository->create(array_merge($data, ["address_type" => '1-Contact', "contact_address" => $contact_address, "active" => true, "is_customer" => true, 'is_company' => $is_company ?? false]));
            $customer = $this->customer_repository->create(array_merge($data, ["partner_id" => $partner->id]));
            if (!empty($data['bank_id'])) {
                $this->bank_repository->create([
                    'account_number' => $data['account_number'],
                    'name' => $data['account_holder'],
                    'account_holder' => $data['account_holder'],
                    'bank_id' => $data['bank_id'],
                    'state' => $data['state'],
                    'partner_id' => $partner->id,
                ]);
            }
            DB::commit();
            return $customer;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function updateCustomer($id, array $data)
    {
        DB::beginTransaction();
        try {
            $customer = $this->customer_repository->update($id, array_merge($data));
            // $customer->update(
            //     array_merge($data)
            // );

            $contact_address = null;

            // if (!empty($data['environmental_code'])) {
            //     $environmental_code = $data['environmental_code'];
            // }

            // if (!empty($data['street'])) {
            //     $contact_address = $data['street'];
            // }
            if (!empty($data['area_id'])) {
                $area_name = Area::find($data['area_id'])->name;
                if (empty($contact_address)) {
                    $contact_address = $area_name;
                }
            }
            if (!empty($data['district_id'])) {
                $district_name = District::find($data['district_id'])->name;
                if (!empty($contact_address)) {
                    $contact_address = $contact_address . ', ' . $district_name;
                } else {
                    $contact_address = $district_name;
                }
            }

            if (!empty($data['province_id'])) {
                $province_name = Province::find($data['province_id'])->name;
                if (!empty($contact_address)) {
                    $contact_address = $contact_address . ', ' . $province_name;
                } else {
                    $contact_address = $province_name;
                }
            }
            $partner = $this->partner_repository->update($customer->partner_id, array_merge($data, [
                "street" => $data['street'],
                'contact_address' => $contact_address
            ]));
            if (!empty($data['bank_id'])) {
                if (empty($data['account_number']) || empty($data['account_holder']) || empty($data['state'])) {
                    abort(400, 'Số tài khoản, tên chủ tài khoản và loại tài khoản ngân hàng không được để trống trường còn lại nếu đã chọn tên ngân hàng');
                }
            }
            if (!empty($data['status'])) {
                if (empty($data['account_number']) || empty($data['account_holder']) || empty($data['bank_id'])) {
                    abort(400, 'Số tài khoản, tên chủ tài khoản và tên ngân khoản ngân hàng không được để trống trường còn lại nếu đã chọn loại ngân hàng');
                }
            }
            if (empty($data['bank_id']) && (!empty($data['account_number']) || !empty($data['account_holder']) || !empty($data['state']))) {
                abort(400, "Vui lòng chọn tên ngân hàng khi đã nhập số tài khoản, tên tài khoản, hoặc loại tài khoản");
            }
            if (empty($data['bank_id']) && !empty($data['id_bank'])) {
                $this->bank_repository->delete($data['id_bank']);
            } else if (empty($data['id_bank'])) {
                if (!empty($data['bank_id']) && !empty($data['account_number']) && !empty($data['account_holder'])) {
                    $this->bank_repository->create([
                        'account_number' => $data['account_number'],
                        'name' => $data['account_holder'],
                        'account_holder' => $data['account_holder'],
                        'bank_id' => $data['bank_id'],
                        'state' => $data['state'],
                        'partner_id' => $partner->id,
                    ]);
                }
            } else {
                $this->bank_repository->update($data['id_bank'], [
                    'account_number' => $data['account_number'],
                    'name' => $data['account_holder'],
                    'account_holder' => $data['account_holder'],
                    'bank_id' => $data['bank_id'],
                    'state' => $data['state']
                ]);
            }
            DB::commit();
            return $customer;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function deleteCustomer($id)
    {
        DB::beginTransaction();
        try {
            $customer = $this->customer_repository->find($id);
            $contract = $this->contract_repository->getModel()::where('sold_to_party_id', $customer->partner_id)->count();
            if ($contract > 0) {
                abort(403, "Không thể xóa khách hàng khi vẫn còn hợp đồng");
            }
            $partner_id = $this->customer_repository->getModel()::where('id', $id)->pluck('partner_id')->first();
            $bank_id = $this->bank_repository->getModel()::where('partner_id', $partner_id)->pluck('id')->first();
            if ($bank_id) {
                $this->bank_repository->delete($bank_id);
            }
            $this->customer_repository->delete($id);
            // if ($partner_id) {
            //     $this->partner_repository->delete($partner_id);
            // }
            $address = $this->partner_repository->getModel()::where('parent_id', $partner_id)->where('title_id', 2)->pluck('id');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
