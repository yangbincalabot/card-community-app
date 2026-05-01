<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WithdrawRequest extends FormRequest
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
            'user_bank_id' => [
                'required',
                Rule::exists('user_banks', 'id')->where(function ($query){
                    $query->where('user_id', $this->user()->id);
                })
            ],
            'money' => 'required|numeric|min:0.01',
            'cash_password' => 'required|max:255'
        ];
    }

    public function attributes()
    {
        return [
            'user_bank_id' => '银行卡',
            'money' => '提现金额',
            'cash_password' => '支付密码'
        ];
    }
}
