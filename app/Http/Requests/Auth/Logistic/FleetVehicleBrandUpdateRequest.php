<?php

namespace App\Http\Requests\Auth\Logistic;

use Illuminate\Foundation\Http\FormRequest;

class FleetVehicleBrandUpdateRequest extends FormRequest
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
        return [
            'code' => 'required|string|max:25|min:1|unique:fleet_vehicle_brands,code,' . $this->code . ',code',
            'name' => 'required|string|max:255|min:1',
            'active' => 'required',
            'description' => 'nullable',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => __('fleet-vehicel-brand.required.name'),
            'code.required' => __('fleet-vehicel-brand.required.code'),
            'code.unique' => __('fleet-vehicel-brand.required.unique'),
            'active.required' => __('fleet-vehicel-brand.required.active'),
            'code.max' => __('fleet-vehicel-brand.required.max')
        ];
    }
    public function attributes()
    {
        return [
            'name' => __('fleet-vehicel-brand.field.name'),
            'code' => __('fleet-vehicel-brand.field.code')
        ];
    }
}
