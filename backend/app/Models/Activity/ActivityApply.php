<?php

namespace App\Models\Activity;

use App\Models\Carte;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class ActivityApply extends Model
{
    // 活动报名表
    protected $table = 'activity_apply';
    protected $fillable = [
        'uid',
        'aid',
        'sid',
        'name',
        'phone',
        'company_name',
        'price',
        'order_no',
        'payment_no',
        'pay_type',
        'pay_status',
        'refund_status',
        'refund_at',
        'extra',
        'refund_no',
        'paid_at',
        'status',
        'commission_status'
    ];

    protected $dates = [
        'paid_at',
    ];

    protected $casts = [
        'extra'     => 'json',
    ];

    protected $appends = [
        'status_show','pay_type_show'
    ];

    // 分佣状态 1：已分佣 2： 未分佣
    const COMMISSION_STATUS_ONE = 1;
    const COMMISSION_STATUS_TWO = 2;

    //报名状态 1: 已报名 2：未完成报名 3：取消报名 :99：已删除
    const STATUS_COMPLETED = 1;
    const STATUS_UNDONE = 2;
    const STATUS_CANCEL = 3;
    const STATUS_DELETED = 99;

    // 支付状态 1: 不需要支付(免费)  2：已支付 3 : 未支付 4：支付超时
    const PAY_STATUS_NO_NEED = 1;
    const PAY_STATUS_PAID = 2;
    const PAY_STATUS_PENDING = 3;
    const PAY_STATUS_TIMEOUT = 4;

    // 退款状态：1.不可退款  2.可退款；3.退款成功；4.退款失败；5.退款中
    const REFUND_STATUS_NOT = 1;
    const REFUND_STATUS_REFUNDABLE = 2;
    const REFUND_STATUS_SUCCESS = 3;
    const REFUND_STATUS_FAILED = 4;
    const REFUND_STATUS_PROCESSING = 5;

    // 验证状态
    const CHECK_STATUS_ONE = 1;
    const CHECK_STATUS_TWO = 2;

    const PAY_TYPE_WECHAT = 1; // 微信
    const PAY_TYPE_BALANCE = 2; // 余额

    public static function getIsRefund ($data) {
        if (!empty($data) && $data->refund_status == self::REFUND_STATUS_REFUNDABLE) {
            $res = Activity::where('id',$data->aid)->select('id','activity_time','apply_end_time')->first();
            if (!empty($res)) {
                $apply_end_time = strtotime($res->apply_end_time);
                if ($apply_end_time < time()) {
                    ActivityApply::where('id', $data->id)->update(['refund_status' => self::REFUND_STATUS_NOT]);
                    return false;
                } else {
                    return true;
                }
            }
        }
        return false;
    }

    // 组装推荐数组
    public function getRefundData($row) {
        $data = [];
        $data['id'] = $row['id'];
        $data['actionUrl'] = route('admin.apply_refund.index');
        $data['redirectUrl'] = '';
        return $data;
    }

    public function getRefundShow ($type='') {
        $data = [
            self::REFUND_STATUS_NOT => '不可退款',
            self::REFUND_STATUS_REFUNDABLE => '可退款',
            self::REFUND_STATUS_SUCCESS => '退款成功',
            self::REFUND_STATUS_FAILED => '退款失败',
            self::REFUND_STATUS_PROCESSING => '退款中',
        ];
        return $data[$type] ?? $data;
    }

    public function getStatusShow($status = ''){
        if (!empty($this->refund_status) && !in_array($this->refund_status,[self::REFUND_STATUS_REFUNDABLE, self::REFUND_STATUS_NOT]) ) {
            return self::getRefundShow($this->refund_status);
        }
        $data = [
            self::STATUS_COMPLETED => '报名成功',
            self::STATUS_UNDONE => '未支付',
            self::STATUS_CANCEL => '已取消',
            self::STATUS_DELETED => '已删除',
        ];
        return $data[$status] ?? '异常状态';
    }

    public function getPayStatusInfo($status = ''){
        $data = [
            self::PAY_STATUS_NO_NEED => '不需要支付(免费)',
            self::PAY_STATUS_PAID => '已支付',
            self::PAY_STATUS_PENDING => '待支付',
            self::PAY_STATUS_TIMEOUT => '支付超时'
        ];
        return $data[$status] ?? '异常状态';
    }

    public function getCheckStatus ($type='') {
        $data = [
            self::CHECK_STATUS_ONE => '正常',
            self::CHECK_STATUS_TWO => '您已经报名该活动，不可重复报名'
        ];
        return $data[$type] ?? $data;
    }

    public function getPayTypeShow ($type='') {
        $data = [
            self::PAY_TYPE_WECHAT => '微信支付',
            self::PAY_TYPE_BALANCE => '余额支付'
        ];
        return $data[$type] ?? $data;
    }

    public function getStatusShowAttribute() {
        if ($this->status == self::STATUS_COMPLETED) {
            // 如果报名成功，则查看报名的该活动
            $res = Activity::where('id',$this->aid)->select('id','activity_time','apply_end_time')->first();
            if (!empty($res)) {
                $tomorrow = Carbon::tomorrow();
                $activity_time = Carbon::parse($res->activity_time);
                if ($tomorrow->gt($activity_time)) {
                    return '已完成';
                }
            }
        }

        return self::getStatusShow($this->status);
    }

    public function getPayTypeShowAttribute() {
        if ($this->pay_type) {
            return self::getPayTypeShow($this->pay_type);
        }
    }

    public static function getAvailableRefundNo()
    {
        do {
            // Uuid类可以用来生成大概率不重复的字符串
            $no = Uuid::uuid4()->getHex();
            // 为了避免重复我们在生成之后在数据库中查询看看是否已经存在相同的退款订单号
        } while (self::query()->where('refund_no', $no)->exists());

        return $no;
    }

    public function activity(){
        return $this->belongsTo(Activity::class,'aid')->select('id', 'cover_image', 'activity_time', 'address_title','title', 'type', 'visits');
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function carte(){
        return $this->belongsTo(Carte::class,'uid','uid')
            ->select('id', 'uid', 'name', 'company_name', 'phone', 'position', 'avatar', 'address_title');
    }

    // 报名规格
    public function specification(){
        return $this->belongsTo(Specification::class,'sid');
    }
}
