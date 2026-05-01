<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TricksRequest extends FormRequest
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
            'content' => ['required'],
            'aid' => ['required','integer'],
        ];
    }

    public function attributes()
    {
        return [
            'content' => '活动内容',
            'aid' => '活动id',
        ];
    }
}
