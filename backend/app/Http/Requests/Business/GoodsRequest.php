<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class GoodsRequest extends FormRequest
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
            'title' => 'sometimes|required|max:30',
            'image' => 'sometimes|required',
            'price' => 'sometimes|required|numeric',
            'content' => 'sometimes|required',
            'is_show' => 'sometimes|boolean',
        ];
    }

    public function attributes()
    {
        return [
            'title' => '标题',
            'image' => '封面',
            'price' => '价格',
            'content' => '详情',
        ];
    }
}
