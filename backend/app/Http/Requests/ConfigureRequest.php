<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfigureRequest extends FormRequest
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
//            'SOCIETY_NAME' => 'required',
            'ASSOCIATION_NAME_1' => 'sometimes|required',
            'ASSOCIATION_NAME_DEFAULT' => 'sometimes|required',
            'SETTLE_RATE' => 'numeric|min:0',
            'SETTLE_TIME' => 'numeric|min:0',
            'BUSINESS_COST' => 'numeric|min:0',
            'SCAN_NUMS' => 'numeric|min:0',
            'SMS_NUMS' => 'numeric|min:0',

        ];
    }
}
