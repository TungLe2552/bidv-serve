<?php

namespace App\Http\Requests\Auth\Ba;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleStoreRequest extends FormRequest
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
    public function rules()
    {
        return [
            'command' => 'required|string|max:100|min:1|unique:schedules,command',
            'expression' => 'required|cron|string|max:50|min:1|',
            'log_filename' => 'required|string|max:50|min:1',
        ];
    }

    public function messages()
    {
        return [
            'command.required' => 'Câu lệnh là bắt buộc',
            'command.unique' => 'Câu lệnh là duy nhất',
            'expression.required' => 'Biểu thức cron là bắt buộc',
            'log_filename.required' => 'Tên file là bắt buộc',
            'expression.cron' => 'Định dạng cron không đúng',
        ];
    }
}
