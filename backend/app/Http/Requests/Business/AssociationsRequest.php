<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class AssociationsRequest extends FormRequest
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
            'image' => 'required',
            'name' => 'required|max:30',
            'desc' => 'required',
            'fee' => 'numeric|min:0',
            'service_desc' =>  'required'
        ];
    }

    public function attributes()
    {
        return [
            'image' => '封面图',
            'name' => '协会名称',
            'desc' => '协会介绍',
            'fee' => '加入协会费用',
            'service_desc' => '服务简介'
        ];
    }
}
