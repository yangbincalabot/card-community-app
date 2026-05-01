<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceiveCarteDetailRequest extends FormRequest
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
            'id' => 'required|exists:receive_cartes',
            'tag_title' => 'sometimes|max:255'
        ];
    }

    public function attributes()
    {
        return [
            'tag_title'=> '标记内容'
        ];
    }
}
