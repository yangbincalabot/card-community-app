<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SpecificationRequest extends FormRequest
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
            'title' => ['required','max:220'],
            'price' => ['required','numeric'],
            'stint' => ['required','integer'],
        ];
    }

    public function attributes()
    {
        return [
            'title' => '名称',
            'price' => '价格',
            'stint' => '限制名额',
        ];
    }
}
