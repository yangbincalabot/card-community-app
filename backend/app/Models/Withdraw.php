<?php

namespace App\Models;


use App\Models\User\UserBank;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Withdraw extends Model
{
    use SoftDeletes;

  //  protected $appends = ['card_tail'];

    const CARD_NUMBER_DIGIT_INDEX = -4; // 尾号开始位置

    protected $fillable = [
        'user_id', 'user_bank_id',  'money', 'status', 'remark'
    ];

    protected $dates = ['deleted_at'];

    const WITHDRAW_STATUS_SUCCESS = 1; // 提现成功
    const WITHDRAW_STATUS_STAY = 2; // 待审核
    const WITHDRAW_STATUS_FAIL = 3; // 审核失败

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function userBank(){
        return $this->belongsTo(UserBank::class, 'user_bank_id');
    }

    public static function getStatus(){
        return [
            self::WITHDRAW_STATUS_STAY => '待审核',
            self::WITHDRAW_STATUS_SUCCESS => '提现成功',
            self::WITHDRAW_STATUS_FAIL => '审核失败'
        ];
    }

    // 银行卡尾号
//    public function getCardTailAttribute(){
//        $card_number = $this->userBank ? $this->userBank->card_number : '';
//        if(empty($card_number)){
//            return '';
//        }
//        $endStr = mb_substr($card_number, self::CARD_NUMBER_DIGIT_INDEX);
//        return Str::start($endStr, str_repeat('**** ', 3));
//    }
}
