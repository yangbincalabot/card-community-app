<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class ActivityRequest extends FormRequest
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
        $title = ($this->type == 1) ? '活动':'会议';
        return [
            'cover_image' => ['required'],
            'title' => ['required','max:220'],
            'type' => ['required','integer'],
            'activity_time' => ['required','date','after:tomorrow'],
            'activity_end_time' => function () use ($title) {
                if ($this->activity_end_time) {
                    $carbon = new Carbon();
                    $activity_time = $carbon::parse($this->activity_time); // 活动时间
                    $activity_end_time = $carbon::parse($this->activity_end_time); // 活动结束时间
                    if (($this->activity_time == $this->activity_end_time) || $activity_time->gt($activity_end_time)) {
                        abort(403, $title.'结束时间必须在'.$title.'时间之后');
                    }
                }
            },
//            'apply_end_time' => ['required','date','after:tomorrow'],
            'apply_end_time' => function () use ($title) {
                if ($this->apply_end_time) {
                    $carbon = new Carbon();
                    $activity_time = $carbon::parse($this->activity_time); // 活动时间
                    $apply_end_time = $carbon::parse($this->apply_end_time); // 活动截止时间
                    $tomorrow = Carbon::tomorrow();
                    if ($tomorrow->gt($apply_end_time)) {
                        abort(403, '报名截止时间必须在今天之后');
                    }
                    if ($apply_end_time->gt($activity_time)) {
                        abort(403, $title.'时间不可在报名截止时间之前');
                    }
                }
            },
            'content' => ['required'],
        ];
    }

    public function attributes()
    {
        $title = ($this->type == 1) ? '活动':'会议';
        return [
            'cover_image' => '封面图',
            'title' => '标题',
            'type' => '类型',
            'activity_time' => $title.'时间',
            'apply_end_time' => '报名截止时间',
            'content' => '内容',
        ];
    }

    public function messages()
    {
        $title = ($this->type == 1) ? '活动':'会议';
        return [
            'activity_time.after' => $title.'时间必须要在今天之后',
        ];
    }
}
