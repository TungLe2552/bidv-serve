<?php

namespace App\Services\BBGN;

use App\Models\BA\BBGN\FleetGoodDeliveryReceipt;
use App\Models\Logistic\FleetDoNotification;
use App\Models\Logistic\FleetGdrStatus;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FleetGoodDeliveryReceiptService
{
    protected $repository;
    protected $statusRepository;
    public function __construct()
    {
        $this->repository = new BaseRepository(FleetGoodDeliveryReceipt::class, [FleetGoodDeliveryReceipt::LOG_NAME]);
    }
    public function getDetails($id)
    {
        $model = $this->repository->find($id);
        $result = $model->load(['gdrLines', 'partner.customers', 'partner.resContract', 'partner.contact', 'partner.address', 'vehicle.driver.partner', 'salesPerson', 'paymentTerm', 'paymentType', 'updatedBy']);
        return $result;
    }
    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            $uniqueString = uniqid();

// Lấy 4 ký tự cuối cùng của chuỗi duy nhất
            $randomString = substr($uniqueString, -4);

            $user_id = Auth::user()->id;
            // Temporary set fleet_delivery_order_id to null since there hasn't been any delivery order yet!
            // if (!empty($data['fleet_delivery_order_id'])) {
            //     $data['fleet_delivery_order_id'] = null;
            // }
            if (empty($data['status_id'])) {
                $statuses = FleetGdrStatus::get()->mapWithKeys(function ($item, $key) {
                    return [$item['code'] => $item['id']];
                });
                $data['status_id'] = $statuses[FleetGdrStatus::DEFAULT_STATUS_CODE];
            }
            if (empty($data['fleet_do_notification_id'])) {
                $data['fleet_do_notification_id'] = $data['id'];
            }
            if (empty($data['code'])) {
                $data['code'] = $randomString;
            }
            if (empty($data['address_id'])) {
                $data['address_id'] = $data['destination_id'];
            }
            if (empty($data['gdr_date'])) {
                $data['gdr_date'] = $data['tran_order_date'];
            }
            if (empty($data['req_date'])) {
                $data['req_date'] = $data['tran_order_date'];
            }
            if (empty($data['actual_gdr_date'])) {
                $data['actual_gdr_date'] = $data['tran_order_date'];
            }

            if (!empty($data['fleet_gdr_lines'])) {
                $fleetGDRLinesData = $data['fleet_gdr_lines'];
                $fleetGDR = $this->repository->create(array_merge($data, [
                    'updated_by_id' => $user_id,
                    'created_by_id' => $user_id,
                    'is_deleted' => false,
                ]));

                if (!empty($fleetGDR)) {
                    FleetDoNotification::where('id', $data['id'])->update([
                        'fleet_good_delivery_receipt_id' => $fleetGDR->id,
                    ]);
                }
                foreach ($fleetGDRLinesData as $key => $lines) {
                    $fleetGDRLines[] = $fleetGDR->gdrLines()->create([
                        'item_id' => $lines['item_id'],
                        'name' => !empty($lines['name_phu_luc']) ? $lines['name_phu_luc'] : null,
                        'production_unit_price' => !empty($lines['production_unit_price']) ? $lines['production_unit_price'] : null,
                        'production_quantity' => !empty($lines['quantity']) ? $lines['quantity'] : null,
                        'production_amount' => !empty($lines['sub_production_amount']) ? $lines['sub_production_amount'] : null,
                        'purchasing_unit_price' => !empty($lines['purchasing_unit_price']) ? $lines['purchasing_unit_price'] : null,
                        'purchasing_quantity' => !empty($lines['purchasing_quantity']) ? $lines['purchasing_quantity'] : null,
                        'purchasing_amount' => !empty($lines['sub_purchasing_amount']) ? $lines['sub_purchasing_amount'] : null,
                        'sub_total' => ((!empty($lines['production_unit_price']) ? $lines['production_unit_price'] : 0) * (!empty($lines['quantity']) ? $lines['quantity'] : 0)),
                    ]);
                }
            }

            $result = [$fleetGDR, $fleetGDRLines];
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();
        try {
            $user_id = Auth::user()->id;
            // Temporary set fleet_delivery_order_id to null since there hasn't been any delivery order yet!
            // if (!empty($data['fleet_delivery_order_id'])) {
            //     $data['fleet_delivery_order_id'] = null;
            // }
            $uniqueString = uniqid();

// Lấy 4 ký tự cuối cùng của chuỗi duy nhất
            $randomString = substr($uniqueString, -4);

            if (empty($data['fleet_do_notification_id'])) {
                $data['fleet_do_notification_id'] = $data['id'];
            }
            if (empty($data['code'])) {
                $data['code'] = $randomString;
            }
            if (empty($data['address_id'])) {
                $data['address_id'] = $data['destination_id'];
            }
            if (empty($data['gdr_date'])) {
                $data['gdr_date'] = $data['tran_order_date'];
            }
            if (empty($data['actual_gdr_date'])) {
                $data['actual_gdr_date'] = $data['tran_order_date'];
            }
            if (empty($data['req_date'])) {
                $data['req_date'] = $data['tran_order_date'];
            }

            if (empty($data['status_id'])) {
                $statuses = FleetGdrStatus::get()->mapWithKeys(function ($item, $key) {
                    return [$item['code'] => $item['id']];
                });
                $data['status_id'] = $statuses[FleetGdrStatus::DEFAULT_STATUS_CODE];
            }
            if (!empty($data['fleet_gdr_lines'])) {
                $fleetGDRLinesData = $data['fleet_gdr_lines'];

                $fleetGDR = $this->repository->update($data['fleet_good_delivery_receipt_id'], array_merge($data, [
                    'updated_by_id' => $user_id,
                ]));
                $fleetGDR->gdrLines()->delete();
                foreach ($fleetGDRLinesData as $key => $lines) {
                    $fleetGDRLines[] = $fleetGDR->gdrLines()->create([
                        'item_id' => $lines['item_id'],
                        'name' => !empty($lines['name_phu_luc']) ? $lines['name_phu_luc'] : null,
                        'production_unit_price' => !empty($lines['production_unit_price']) ? $lines['production_unit_price'] : null,
                        'production_quantity' => !empty($lines['quantity']) ? $lines['quantity'] : null,
                        'production_amount' => !empty($lines['sub_production_amount']) ? $lines['sub_production_amount'] : null,
                        'purchasing_unit_price' => !empty($lines['purchasing_unit_price']) ? $lines['purchasing_unit_price'] : null,
                        'purchasing_quantity' => !empty($lines['purchasing_quantity']) ? $lines['purchasing_quantity'] : null,
                        'purchasing_amount' => !empty($lines['sub_purchasing_amount']) ? $lines['sub_purchasing_amount'] : null,
                        'sub_total' => ((!empty($lines['production_unit_price']) ? $lines['production_unit_price'] : 0) * (!empty($lines['quantity']) ? $lines['quantity'] : 0)),
                    ]);
                }
            }
            $result = [$fleetGDR, $fleetGDRLines];
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $result = $this->repository->update($id, [
                'is_deleted' => true,
            ]);
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
