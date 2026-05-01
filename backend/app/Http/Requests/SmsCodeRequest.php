<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SmsCodeRequest extends FormRequest
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
            'phone' => [
                'required',
                'mobile',
                // TODO 后期完善后短信发送后去掉注释
//                Rule::exists('users', 'phone')->where(function ($query){
//                    $query->where('id', $this->user()->id);
//                })
            ]
        ];
    }
}
