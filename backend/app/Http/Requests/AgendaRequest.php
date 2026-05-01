<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgendaRequest extends FormRequest
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
            'start_time' => ['required'],
            'end_time' => ['required','after:start_time'],
            'presenter' => ['required','max:30'],
            'title' => ['required','max:220'],
        ];
    }

    public function attributes()
    {
        return [
            'start_time' => '议程开始时间',
            'end_time' => '议程结束时间',
            'presenter' => '主讲人',
            'title' => '议题',

        ];
    }

}
