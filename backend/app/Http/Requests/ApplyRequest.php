<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyRequest extends FormRequest
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
            'aid' => ['required','integer'],
            'sid' => ['required','integer'],
            'name' => ['required','max:30'],
            'phone' => ['required','mobile'],
            'company_name' => ['required','max:100'],
        ];
    }

    public function attributes()
    {
        return [
            'aid' => '活动',
            'sid' => '规格',
            'name' => '姓名',
            'phone' => '手机号码',
            'company_name' => '工作单位',
        ];
    }

}
