<?php

namespace App\Http\Requests\Auth\Ba\BBGN;

use Illuminate\Foundation\Http\FormRequest;

class FleetGoodDeliveryReceiptStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code'=> 'required|unique:fleet_good_delivery_receipts,code',
            'partner_id'=> 'required|integer',
            'fleet_delivery_order_id' => 'required|integer',
            'customer_gdr_code'=> 'nullable',
            'req_date'=> 'required|date',
            'address_id'=> 'required|integer',
            'bill_to'=> 'nullable|integer',
            'gdr_date'=> 'required|date',
            'vehicle_id'=> 'nullable|integer',
            'status_id'=> 'required|integer',
            'actual_gdr_date'=> 'nullable|date',
            'attention_from_customer'=> 'nullable',
            'attention_from_internal'=> 'nullable|integer',
            'sales_person_id'=> 'nullable|integer',
            'payment_term_id'=> 'nullable|integer',
            'payment_type_id'=> 'nullable|integer',
            'production_amount'=> 'nullable|numeric',
            'purchasing_amount'=> 'nullable|numeric',
            'total'=> 'nullable|numeric',
            'fleet_gdr_lines' => 'required|array|min:1',
            'fleet_gdr_lines.*.item_id'=> 'required|integer',
        ];
    }
}
