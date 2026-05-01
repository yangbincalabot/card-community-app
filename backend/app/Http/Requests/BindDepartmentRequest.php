<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BindDepartmentRequest extends FormRequest
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
            'carte_id' => 'required|exists:carte,id',
            'department_id' => 'required|exists:departments,id'
        ];
    }

    public function messages()
    {
        return [
            'carte_id.required' => '非法操作',
            'carte_id.exists' => '名片不存在',
            'department_id.required' => '请选择所在部门',
            'department_id.exists' => '部门不存在',
        ];
    }
}
