<?php

namespace App\Http\Requests;

use App\Models\CompanyCard;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCarteRequest extends FormRequest
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
        $base_rules = [
            'cid' => [
//                'required',
                // 'exists:company_card,id'
                function($attribute, $value, $fail) {
                    if($value > 0){
                        $companyCard = CompanyCard::find($value);
                        if(!$companyCard){
                            return $fail('该公司不存在');
                        }
                        if($companyCard->status !== CompanyCard::TYPE_NORMAL){
                            return $fail('该公司不允许绑定');
                        }
                    }
                },
            ],
            'name' => 'required|max:20',
            'company_name' => 'required|max:255',
//            'industry_id' => 'required|exists:industries,id',
//            'position' => 'required|max:255',
            'phone' => 'required|mobile',
            'avatar' => 'max:255',
            'wechat' => 'max:255',
            'address_title' => 'max:255',
//            'address_name' => 'required|max:255',
//            'longitude' => 'required|max:30',
            'latitude' => 'max:30',
            'open' => 'required|between:1,2', // 是否公开
        ];
        if(!empty($this->get('email'))){
            $base_rules['email'] = 'email|max:255';
        }
        return $base_rules;
    }

    public function attributes()
    {
        return [
            'name' => '姓名',
            'company_name' => '公司名称',
            'avatar' => '头像',
            'phone' => '手机号码',
            'wechat' => '微信号',
            'email' => '邮箱',
            'introduction' => '简介',
            'position' => '职务',
            'images' => '相册',
            'longitude' => '经度',
            'latitude' => '纬度',
            'address_title' => '地址',
            'cid' => '公司',
            'industry_id' => '行业',
            'tags' => '标签',
            'open' => '名片公开',
            'address_name' => '地址简介',
        ];
    }

    public function messages()
    {
        return [
            'longitude.required' => '请设置经纬度',
            'latitude.required' => '请设置经纬度',
        ];
    }
}
