<?php

namespace App\Http\Requests;

use App\Models\Bank;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserBankRequest extends FormRequest
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
            'bank_id' => [
                'numeric',
                'min:1',
                Rule::exists('banks', 'id')->where(function ($query){
                    $query->where('is_use', Bank::IS_USER_TRUE)->whereNull('deleted_at');
                }),
            ],
            'card_name' => 'required',
            'card_number' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $valueLenth = strlen($value);
                    if(!($valueLenth >= 16 && $valueLenth <= 19)){
                        return $fail('银行卡号长度必须在16到19之间');
                    }
                }
            ]
        ];
    }

    public function attributes()
    {
        return [
            'card_name' => '持卡人',
            'card_number' => '卡号',
            'bank_id' => '银行卡'
        ];
    }
    public function messages()
    {
        return [
            'bank_id.numeric' => '请选择正确的银行卡',
            'bank_id.min' => '请选择正确的银行卡',
        ];
    }
}
