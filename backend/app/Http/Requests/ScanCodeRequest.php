<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScanCodeRequest extends FormRequest
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
            'from_user_id' => 'required|exists:users,id',
            'address_title' => 'required|max:191',
            'longitude' => 'max:191',
            'latitude' => 'max:191',
            'address_name' => 'max:191'
        ];
    }

    public function attributes()
    {
        return [
             'from_user_id' => '名片用户',
            'address_title' => '互换地址',
            'longitude' => '经度',
            'latitude' => '纬度',
            'address_name' => '地址简称'
        ];
    }
}
