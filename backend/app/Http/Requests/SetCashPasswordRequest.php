<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetCashPasswordRequest extends FormRequest
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
                'required',
                'numeric',
                'confirmed',
                function($attribute, $value, $fail){
                    if(!$this->checkLength($value, 6)){
                        return $fail('新密码为6个字符');
                    }
                }
            ],
            'cash_password_confirmation' => [
                'required',
                'numeric',
                function($attribute, $value, $fail){
                    if(!$this->checkLength($value, 6)){
                        return $fail('确认密码为6个字符');
                    }
                }
            ]
        ];
    }

    public function attributes()
    {
        return [
            'cash_password' => '新密码',
            'cash_password_confirmation' => '确认密码'
        ];
    }
    public function messages()
    {
        return [
            'cash_password.confirmed' => '两次输入密码不一致'
        ];
    }

    private function checkLength($value, $targetLength){
        $valueLenth = strlen($value);
        if($valueLenth != $targetLength){
            return false;
        }
        return true;
    }
}
