<?php

namespace App\Http\Requests\Auth\Ba\Contract;

use Illuminate\Foundation\Http\FormRequest;

class ContractAppendixStoreRequest extends FormRequest
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
            'contract_id' => 'required|integer',
            'appendix_number' => 'required',
            'signing_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'notes' => 'nullable',
            'contract_appendix_lines' => 'required|array|min:1',
            'contract_appendix_lines.*.item_id' => 'integer',
            'contract_appendix_lines.*.production_unit_price' => 'numeric',
            'contract_appendix_lines.*.purchasing_unit_price' => 'numeric',
        ];
    }
    public function messages()
    {
        return [
            'contract_id.required' => 'Mã hợp đồng không được bỏ trống.',
            'contract_id.integer' => 'Mã hợp đồng phải là một số nguyên.',
            'appendix_number.required' => 'Số thứ tự phụ lục hợp đồng là bắt buộc',
            'signing_date.date' => 'Ngày ký hợp đồng không hợp lệ.',
            'start_date.date' => 'Ngày hiệu lực không hợp lệ.',
            'end_date.date' => 'Ngày hết hiệu lực không hợp lệ.',
            'end_date.after' => 'Ngày hết hiệu lực phải lớn hơn ngày hiệu lực.',
            'contract_appendix_lines.required' => 'Chi tiết phụ lục phải có ít nhất một sản phẩm.',
            'contract_appendix_lines.array' => 'Chi tiết phục lục phải là một mảng các giá trị.',
            'contract_appendix_lines.min' => 'Chi tiết phục lục phải có ít nhất một sản phẩm.',
            'contract_appendix_lines.*.item_id.required' => 'Mã sản phẩm không được bỏ trống.',
            'contract_appendix_lines.*.item_id.integer' => 'Mã sản phẩm phải là một số nguyên.',
            'contract_appendix_lines.*.production_unit_price.numeric' => 'Đơn giá sản xuất phải là dạng số.',
            'contract_appendix_lines.*.purchasing_unit_price.numeric' => 'Đơn giá bán phải là dạng số.',
        ];
    }
}
