<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * 会员认证
 * Class Membership
 * @package App\Models
 */
class Membership extends Model
{
    protected $guarded = ['id'];

    const STATUS_UNREVIEWED = 0;
    const STATUS_PASS = 1;
    const STATUS_REFUSE = -1;

    const STATUS_TEXT = [
        self::STATUS_REFUSE => '拒绝',
        self::STATUS_UNREVIEWED => '未审核',
        self::STATUS_PASS => '同意'
    ];

    protected $appends = [
        'status_text'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function association() {
        return $this->belongsTo(Association::class, 'aid');
    }

    public function carte() {
        return $this->belongsTo(Carte::class, 'carte_id');
    }

    public function getStatusTextAttribute() {
        return self::STATUS_TEXT[$this->attributes['status']] ?? '';
    }


}
