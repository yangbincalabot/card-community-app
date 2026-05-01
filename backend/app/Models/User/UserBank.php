<?php

namespace App\Models\User;

use App\Models\Bank;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserBank extends Model
{
    protected $fillable = [
        'user_id', 'bank_id', 'card_name', 'card_number'
    ];
    protected $appends = ['card_tail'];

    const CARD_NUMBER_DIGIT_INDEX = -4; // 尾号开始位置

    public function bank(){
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    // 银行卡尾号
    public function getCardTailAttribute(){
        $endStr = mb_substr($this->attributes['card_number'], self::CARD_NUMBER_DIGIT_INDEX);
        return Str::start($endStr, str_repeat('**** ', 4));
    }

}
