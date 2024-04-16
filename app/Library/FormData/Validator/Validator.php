<?php

namespace App\Library\FormData\Validator;

use App\Helpers\Dynamic\TableHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Validator
{
    private $validator;
    private $model;
    public function __construct(string $table_name)
    {
        $this->model = TableHelper::getTableByCode($table_name);
        switch ($table_name) {
            case 'ba_contract_overall_statuses':
                $this->validator = new ContractOverallStatusesValidator;
                break;
            case 'norms_salary_worker_lines':
                $this->validator = new NormSalaryWorkerLineValidator;
                break;
            case 'norm_partner_location_territories':
                $this->validator = new NormPartnerLocationTerritoryValidator;
                break;
            case 'fleet_norms_freight_rates':
                $this->validator = new FleetNormsFreightRates;
                break;
            case 'norms_salary_worker_territories':
                $this->validator = new NormSalaryWorkerTerritoryValidator;
                break;
            case 'fleet_type_of_transports':
                $this->validator = new FleetTypeOfTransportValidator;
                break;
            case 'fleet_vehicle_brands':
                $this->validator = new FleetVehicleBrandValidator;
                break;
            case 'fleet_vehicle_models':
                $this->validator = new FleetVehicleModelValidator;
                break;
            case 'fleet_vehicles':
                $this->validator = new FleetVehicleValidator;
                break;
            case 'res_territories':
                $this->validator = new TerritoryValidator;
                break;
            case 'hr_departments':
                $this->validator = new HrDepartmentValidator;
                break;
            case 'hr_employee_categories':
                $this->validator = new HrEmployeeCategoryValidator;
                break;
            case 'res_units':
                $this->validator = new ResUnitValidator;
                break;
            case 'fleet_tran_order_statuses':
                $this->validator = new FleetTranOderStatusesValidator;
                break;
            case 'fleet_gdr_statuses':
                $this->validator = new FleetGdrStatusesValidator;
                break;
            case 'fleet_fuel_type_details':
                $this->validator = new FleetFuelTypeDetailsValidator;
                break;
            case 'fleet_refusal_reasons':
                $this->validator = new FleetRefusalReasonsValidator;
                break;
            case 'fleet_norm_types':
                $this->validator = new FleetNormTypesValidator;
                break;
            case 'ba_contract_appendix_statuses':
                $this->validator = new ContractAppendixStatusValidator;
                break;
            case 'im_picking_types':
                $this->validator = new ImPickingTypeValidator;
                break;
            case 'fleet_node_types':
                $this->validator = new FleetNodeTypesValidator;
                break;
            case 'fleet_route_monitoring':
                $this->validator = new FleetRouteMonitoringValidator;
                break;
            case 'fleet_route_types':
                $this->validator = new FleetRouteTypesValidator;
                break;
            case 'res_partner_titles':
                $this->validator = new ResPartnerTitlesValidator;
                break;
            case 'res_partner_categories':
                $this->validator = new ResPartnerCategoriesValidator;
                break;
            case 'fleet_req_statuses':
                $this->validator = new FleetReqStatusesValidator;
                break;
            case 'hr_payslip_runs':
                $this->validator = new HrPayslipRunsValidator;
                break;
            case 'fleet_maintenance_requests':
                $this->validator = new FleetMaintenanceRequestsValidator;
                break;
            case 'ir_ui_menus':
                $this->validator = new IrUiMenusValidator;
                break;
            case 'ba_customer_classifications':
                $this->validator = new BaCustomerClassificationsValidator;
                break;
            case 'res_loading_methods':
                $this->validator = new ResLoadingMethodValidator;
                break;
        }
    }

    public function getRules(array $data = [], Model $model = null)
    {

        $rules = [];
        if (!empty($this->model)) {
            $this->model->fields->each(function ($field) use (&$rules) {
                if (empty($field->domain)) {
                    return;
                }
                $rule = explode('|', $field->domain);
                $rules[$field['code']] = $rule;
            });
        }
        if (!empty($this->validator)) {
            $rules = array_merge($this->validator->getRules($data, $model));
        }
        return $rules;
    }
    public function getMessages(array $data = [], Model $model = null)
    {

        $messages = [];
        if (!empty($this->validator)) {
            $messages = array_merge($this->validator->getMessages($data, $model));
        }
        return $messages;
    }
}

class NormSalaryWorkerLineValidator extends AValidator
{
    public function getRules(array $data = [], Model $model = null)
    {
        return [
            'norms_id' => ['required'],
            'unit_price' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'unit_id' => ['required'],
            'type' => ['required'],
        ];
    }
}
class FleetRouteMonitoringValidator extends AValidator
{
    public function getRules(array $data = [], Model $model = null)
    {
        return [
            'vehicle_id' => ['required'],
            'driver_id' => ['required'],
            'route_id' => ['required'],
            'unit_id' => ['required'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'amount_fuel' => ['required', 'numeric', 'gt:0'],
        ];
    }
}
class NormPartnerLocationTerritoryValidator extends AValidator
{
    public function getRules(array $data = [], Model $model = null)
    {
        return [
            'territory_id' => ['required'],
            'partner_id' => ['required'],
            'start_date' => ['before:end_date'],
            'end_date' => ['after:start_date'],
            'coefficient_salary' => ['required', 'numeric', 'min:0'],
        ];
    }
}
class FleetNormsFreightRates extends AValidator
{
    public function getRules(array $data = [], Model $model = null)
    {
        return [
            'code' => ['required', 'string', 'min:0', 'max:255', Rule::unique('fleet_norms_freight_rates', 'code')->ignore($model !== null ? $model->id : null, 'id')],
            'name' => ['string', 'min:0', 'max:255'],
            'threshold_value' => ['numeric'],
        ];
    }
}
class NormSalaryWorkerTerritoryValidator extends AValidator
{
    public function getRules(array $data = [], Model $model = null)
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('norms_salary_worker_territories', 'code')->ignore($model !== null ? $model->id : null, 'id')],
            'name' => ['required', 'string', 'max:255'],
            'coefficient_salary' => ['required', 'numeric', 'min:0'],
        ];
    }
}

class FleetVehicleValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {
        return [
            'license_plate' => ['max:15'],
            'location' => ['max:2555'],
            'chassis_number' => ['max:35'],
            'color' => ['max:35'],
            'vehicle_registration_name' => ['max:100'],
        ];
    }
}

class ContractAppendixStatusValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {
        if (empty($model)) {
            return [
                'code' => ['required', 'string', 'max:255', 'min:1', Rule::unique('ba_contract_appendix_statuses', 'code')],
                'name' => ['required', 'string', 'max:255', 'min:1'],
            ];
        } else {
            return [
                'code' => ['required', 'string', 'max:255', 'min:1', Rule::unique('ba_contract_appendix_statuses', 'code')->ignore($model->id, 'id')],
                'name' => ['required', 'string', 'max:255', 'min:1'],
                'allowance' => ['required'],
                'active' => ['required'],
            ];
        }
    }
}

class ImPickingTypeValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {
        if (empty($model)) {
            return [
                'code' => ['required', 'string', 'max:100', 'min:1', Rule::unique('im_picking_types', 'code')],
                'name' => ['required', 'string', 'max:255', 'min:1'],
                'type' => ['required'],
                'active' => ['required'],
            ];
        } else {
            return [
                'code' => ['required', 'string', 'max:100', 'min:1', Rule::unique('im_picking_types', 'code')->ignore($model->id, 'id')],
                'name' => ['required', 'string', 'max:255', 'min:1'],
                'type' => ['required'],
                'active' => ['required'],
            ];
        }
    }
}

class FleetVehicleModelValidator
{

    public function getRules(array $data = [], Model $model = null)
    {
        return [
            'code' => ['max:25'],
            'short_name' => ['max:10'],
        ];
    }
}

class ContractOverallStatusesValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {
        return [
            'code' => ['max:10'],
        ];
    }
}
class FleetTypeOfTransportValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {
        return [
            'code' => ['max:25'],
            'name' => ['max:25'],
        ];
    }
}
class FleetVehicleBrandValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {
        return [
            'code' => ['max:25'],
            'name' => ['max:25'],
        ];
    }
}
class TerritoryValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {
        return [
            'code' => ['max:10'],
            'name' => ['max:255'],
        ];
    }
}

class HrDepartmentValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {
        if (empty($model)) {
            return [
                'code' => ['required', 'string', 'max:10', 'min:1', Rule::unique('hr_departments', 'code')],
                'name' => ['required', 'string', 'max:50', 'min:1'],
            ];
        } else {
            $descendant_ids = $model->descendants()->get(['id', 'parent_id'])->pluck('id');
            return [
                'code' => ['required', 'string', 'max:10', 'min:1', Rule::unique('hr_departments', 'code')->ignore($model->id, 'id')],
                'name' => ['required', 'string', 'max:50', 'min:1'],
                'parent_id' => ['different:id', Rule::notIn($descendant_ids)],
            ];
        }
    }
    public function getMessages(array $data = [], Model $model = null)
    {
        return [
            'parent_id' => [
                "different" => "Dữ liệu không thể thuộc chính bản thân",
                "not_in" => "Dữ liệu không thể thuộc dữ liệu con của bản thân",
            ],
        ];
    }
}
class HrEmployeeCategoryValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {

        if (empty($model)) {

            return [
                'name' => ['required', 'string', 'max:255', 'min:1'],
            ];
        } else {
            return [
                'name' => ['required', 'string', 'max:255', 'min:1'],
            ];
        }
    }
}
class FleetFuelTypeDetailsValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {

        if (empty($model)) {

            return [
                'name' => ['required', 'string', 'max:255', 'min:1'],
            ];
        } else {
            return [
                'name' => ['required', 'string', 'max:255', 'min:1'],
            ];
        }
    }

    public function getMessages(array $data = [], Model $model = null)
    {
        return [
            'name' => [
                "required" => "Trường tên không được phép để trống.",
                "max" => "Trường tên không được dài quá 255 ký tự.",
            ],
        ];
    }
}

class FleetNormTypesValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {

        if (empty($model)) {

            return [
                'code' => ['required', 'string', 'max:10', 'min:1'],
                'name' => ['required', 'string', 'max:255', 'min:1'],

            ];
        } else {
            return [
                'code' => ['required', 'string', 'max:10', 'min:1'],
                'name' => ['required', 'string', 'max:255', 'min:1'],
            ];
        }
    }

    public function getMessages(array $data = [], Model $model = null)
    {
        return [
            'name' => [
                "required" => "Trường tên không được phép để trống.",
                "max" => "Trường tên không được dài quá 255 ký tự.",
            ],
            'code' => [
                "required" => "Trường mã không được phép để trống.",
                "max" => "Trường mã không được dài quá 10 ký tự.",
            ],
        ];
    }
}
class FleetRefusalReasonsValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {

        if (empty($model)) {

            return [
                'code' => ['required', 'string', 'max:10', 'min:1'],
                'name' => ['required', 'string', 'max:255', 'min:1'],

            ];
        } else {
            return [
                'code' => ['required', 'string', 'max:10', 'min:1'],
                'name' => ['required', 'string', 'max:255', 'min:1'],
            ];
        }
    }

    public function getMessages(array $data = [], Model $model = null)
    {
        return [
            'name' => [
                "required" => "Trường tên không được phép để trống.",
                "max" => "Trường tên không được dài quá 255 ký tự.",
            ],
            'code' => [
                "required" => "Trường mã không được phép để trống.",
                "max" => "Trường mã không được dài quá 10 ký tự.",
            ],
        ];
    }
}

class ResUnitValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {

        if (empty($model)) {

            return [
                'code' => ['required', 'string', 'max:10', 'min:1'],
                'category_id' => ['required'],
                'name' => ['required', 'string', 'max:255', 'min:1'],

            ];
        } else {
            return [
                'code' => ['required', 'string', 'max:10', 'min:1'],
                'category_id' => ['required'],
                'name' => ['required', 'string', 'max:255', 'min:1'],
            ];
        }
    }
}

class FleetTranOderStatusesValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {

        if (empty($model)) {
            return [
                'name' => ['required', 'string', 'max:255', 'min:1'],
                'code' => ['required', 'string', 'max:10', 'min:1', Rule::unique('fleet_tran_order_statuses', 'code')],
            ];
        } else {
            return [
                'name' => ['required', 'string', 'max:255', 'min:1'],
                'code' => ['required', 'string', 'max:10', 'min:1', Rule::unique('fleet_tran_order_statuses', 'code')->ignore($model->id, 'id')],
            ];
        }
    }
}

class FleetGdrStatusesValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {

        if (empty($model)) {
            return [
                'name' => ['required', 'string', 'max:255', 'min:1'],
                'code' => ['required', 'string', 'max:10', 'min:1'],
            ];
        } else {
            return [
                'name' => ['required', 'string', 'max:255', 'min:1'],
                'code' => ['required', 'string', 'max:10', 'min:1'],
            ];
        }
    }
}
class FleetRouteTypesValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {

        if (empty($model)) {
            return [
                'name' => ['required', 'string', 'max:255', 'min:1'],
                'code' => ['required', 'string', 'max:10', 'min:1', Rule::unique('fleet_route_types', 'code')],
            ];
        } else {
            return [
                'name' => ['required', 'string', 'max:255', 'min:1'],
                'code' => ['required', 'string', 'max:10', 'min:1', Rule::unique('fleet_route_types', 'code')->ignore($model->id, 'id')],
            ];
        }
    }
}
class FleetNodeTypesValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {

        if (empty($model)) {
            return [
                'name' => ['required', 'string', 'max:255', 'min:1'],
                'code' => ['required', 'string', 'max:10', 'min:1', Rule::unique('fleet_node_types', 'code')],
            ];
        } else {
            return [
                'name' => ['required', 'string', 'max:255', 'min:1'],
                'code' => ['required', 'string', 'max:10', 'min:1', Rule::unique('fleet_node_types', 'code')->ignore($model->id, 'id')],
            ];
        }
    }
}
class ResPartnerTitlesValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['required', 'string', 'max:32'],
            'color' => ['max:9'],
            'domain' => ['required', 'max:128'],
        ];
    }
}

class ResPartnerCategoriesValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {
        return [
            'name' => ['max:255'],
            'short_name' => ['max:32'],
        ];
    }
}

class FleetReqStatusesValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {

        if (empty($model)) {
            return [
                'name' => ['required', 'string', 'max:100', 'min:1'],
                'code' => ['required', 'string', 'max:10', 'min:1', Rule::unique('fleet_req_statuses', 'code')],
            ];
        } else {
            return [
                'name' => ['required', 'string', 'max:100', 'min:1'],
                'code' => ['required', 'string', 'max:10', 'min:1', Rule::unique('fleet_req_statuses', 'code')->ignore($model->id, 'id')],
            ];
        }
    }
}

class HrPayslipRunsValidator extends AValidator
{
    public function getRules(array $data = [], Model $model = null)
    {

        if (empty($model)) {
            return [
                'name' => ['required', 'string', 'max:100', 'min:1'],

            ];
        } else {
            return [
                'name' => ['required', 'string', 'max:100', 'min:1'],
            ];
        }
    }
}

class FleetMaintenanceRequestsValidator extends AValidator
{
    public function getRules(array $data = [], Model $model = null)
    {
        return [
            'name' => ['string', 'max:255'],
        ];
    }
}
class IrUiMenusValidator extends AValidator
{

    public function getRules(array $data = [], Model $model = null)
    {
        return [
            'name' => ['required', 'max:255'],
            'type' => ['required', 'max:20'],
            'route' => ['max:255'],
            'type' => ['required', 'max:20'],
        ];
    }
}
class BaCustomerClassificationsValidator extends AValidator
{
    public function getRules(array $data = [], Model $model = null)
    {

        if (empty($model)) {
            return [
                'code' => ['required', 'string', 'max:50', 'min:1', Rule::unique('ba_customer_classifications', 'code')],
                'name' => ['required', 'string', 'max:255', 'min:1'],
            ];
        } else {
            return [
                'code' => ['required', 'string', 'max:50', 'min:1', Rule::unique('ba_customer_classifications', 'code')->ignore($model->id, 'id')],
                'name' => ['required', 'string', 'max:255', 'min:1'],
            ];
        }
    }
    public function getMessages(array $data = [], Model $model = null)
    {
        return [
            'name' => [
                "required" => "Trường tên không được phép để trống.",
                "max" => "Trường tên không được dài quá 255 ký tự.",
            ],
            'code' => [
                "required" => "Trường mã không được phép để trống.",
                "max" => "Trường mã không được dài quá 50 ký tự.",
                "unique" => "Mã này đã tồn tại trong hệ thống.",
            ],
        ];
    }
}

class ResLoadingMethodValidator extends AValidator
{
    public function getRules(array $data = [], Model $model = null)
    {

        if (empty($model)) {
            return [
                'code' => ['required', 'string', 'max:255', 'min:1', Rule::unique('res_loading_methods', 'code')],
                'name' => ['required', 'string', 'max:255', 'min:1'],
                'active' => ['required', 'boolean'],

            ];
        } else {
            return [
                'code' => ['required', 'string', 'max:255', 'min:1', Rule::unique('res_loading_methods', 'code')->ignore($model->id, 'id')],
                'name' => ['required', 'string', 'max:255', 'min:1'],
                'active' => ['required', 'boolean'],
            ];
        }
    }
}
