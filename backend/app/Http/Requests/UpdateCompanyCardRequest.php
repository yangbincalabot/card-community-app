<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyCardRequest extends FormRequest
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
            'company_name' => [
                'required',
                'max:255',
                Rule::unique('company_card')->ignore($this->user()->id, 'uid'),
            ],
            'logo' => 'required|max:255',
            'contact_number' => [
                'required',
                'max:100',
                function($attribute, $value, $fail) {
                    $is_mobile = match_mobile($value);
                    $is_phone = match_phone($value);
                    if(!$is_mobile && !$is_phone){
                        return $fail('请输入正确的联系电话');
                    }
                },
            ],
            'industry_id' => 'required|exists:industries,id',
            'introduction' => 'required',
//            'website' => 'required|website',
            'images' => 'required',
            'address_title' => 'required',
            'address_name' => 'max:255',
            'longitude' => 'max:30',
            'latitude' => 'max:30',
        ];
    }

    public function attributes()
    {
        return [
            'company_name' => '公司名称',
            'logo' => 'logo',
            'contact_number' => '联系电话',
            'industry_id' => '所属行业',
            'introduction' => '企业简介',
//            'website' => '企业官网',
            'images' => '企业相册',
            'address_title' => '公司地址',
            'longitude' => '经度',
            'latitude' => '纬度',
            'address_name' => '地址简介',
        ];
    }

    public function messages()
    {
        return [
            'contact_number.check_phone' => '请输入正确的联系电话',
//            'website.website' => '请输入正确的网站'
        ];
    }
}
