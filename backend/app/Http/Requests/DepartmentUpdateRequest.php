<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentUpdateRequest extends FormRequest
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
            'id' => 'required|exists:departments,id',
            'name' => 'required|max:30'
        ];
    }

    public function attributes()
    {
        return [
            'name' => '部门名称',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => '非法操作',
            'id.exists' => '部门不存在'
        ];
    }
}
