<?php

namespace App\Http\Requests\Auth\Logistic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FleetVehicleStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'license_plate' => 'unique:fleet_vehicles,license_plate|required|string|max:15|min:1',
            'state_id' => 'nullable|integer',
            'weight' => 'nullable|numeric',
            'weight_unit_id' => 'nullable|integer',
            'max_volume' => 'nullable|numeric',
            'volume_unit_id' => 'nullable|integer',
            'model_id' => 'nullable|integer',
            'chassis_number' => 'nullable|max:40',
            'engine_number' => 'nullable|max:40',
            'ownership' => ['required', Rule::in(['1-Xe công ty', '2-Xe thuê ngoài'])],
            'location' => 'nullable',
            'min_volume' => 'nullable|numeric',
            'manufacture_year' => 'nullable|digits:4|min:1800|max:3000|integer',
            'color' => 'nullable|max:32',
            'fuel_type' => 'nullable',
            'insurance_term' => 'nullable',
            'registration_deadline' => 'nullable',
            'acquisition_date' => 'nullable',
            'odometer' => 'nullable',
            'driver_id' => 'nullable',
            'has_image' => 'boolean|nullable',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048|dimensions:min_width=1024,min_height=1024,max_width=1200,max_height=1200',
            'image_medium' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048|dimensions:min_width=128,min_height=128,max_width=640,max_height=640',
            'image_small' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048|dimensions:min_width=64,min_height=64,max_width=320,max_height=320',
            'vehicle_registration_name' => 'nullable|max:100',
            'vehicle_owner_id' => 'nullable',
            'active' => 'nullable',
            'company_id' => 'nullable',
            'tags_id' => 'nullable',
        ];

        if ($this->min_volume !== null) {
            $rules['max_volume'] = 'nullable|numeric|gte:min_volume';
        }

        return $rules;
    }
    public function messages()
    {
        return [
            'max_volume.gte' => 'Trường :attribute phải lớn hơn hoặc bằng Thể thích tối thiểu',
            'license_plate.unique' => 'Trường :attribute phải là duy nhất',
            'license_plate.required' => 'Trường :attribute phải bắt buộc',
            'license_plate.max' => 'Trường :attribute không được phép vượt quá 15 ký tự.',
            'license_plate.min' => 'Trường :attribute phải tối thiểu có 1 ký tự.',
            'manufacture_year.min' => 'Năm quá nhỏ không phù hợp',
            'ownership.required' => 'Xe công ty/xe ngoài bắt buộc',
            'manufacture_year.max' => 'Năm quá lớn không phù hợp',
            'manufacture_year.digits' => 'Năm phải là 4 chữ số',
            'chassis_number.max' => 'Số khung chỉ được phép có tối đa :max ký tự',
            'engine_number.max' => 'Số máy chỉ được phép có tối đa :max ký tự',
            'color.max' => 'Màu xe chỉ được phép có tối đa :max ký tự',
            'min_volume.gt' => 'Thể tích tối thiểu phải lớn hơn không',
            'vehicle_registration_name.max' => 'Tên đăng kí xe chỉ được phép có tối đa :max ký tự',
        ];
    }
    public function attributes()
    {
        return [
            'license_plate' => __('fleet_vehicles.field.license_plate'),
            'weight' => __('fleet_vehicles.field.weight'),
            'weight_unit_id' => __('fleet_vehicles.field.weight'),
            'max_volume' => __('fleet_vehicles.field.max_volume'),
            'min_volume' => __('fleet_vehicles.field.min_volume'),
            'volume_unit_id' => __('fleet_vehicles.field.volume_unit_id'),
            'model_id' => __('fleet_vehicles.field.model_id'),
            'chassis_number' => __('fleet_vehicles.field.chassis_number'),
            'engine_number' => __('fleet_vehicles.field.engine_number'),
            'ownership' => __('fleet_vehicles.field.ownership'),
        ];
    }
}
