<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class CompanyCardPaymentRequest extends FormRequest
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
            'cash_password' => [
                'sometimes',
                'max:255',
                function($attribute, $value, $fail){
                    // 验证支付密码
                    $user = $this->user('api');
                    if(!$user){
                        return $fail('非法操作');
                    }
                    if(!Hash::check($value, $user->cash_password )){
                        return $fail('支付密码错误');
                    }
                }
            ]
        ];
    }

    public function attributes()
    {
        return [
            'cash_password' => '支付密码'
        ];
    }
}
