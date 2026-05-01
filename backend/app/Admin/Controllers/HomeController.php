<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Activity\ActivityApply;
use App\Models\CompanyCardLog;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Echarts\Echarts;
use Illuminate\Support\Facades\DB;
use App\Models\User\UserBalanceLog;

class HomeController extends Controller
{
    public function index(Content $content)
    {

        $todayIncome = $this->getTodayIncome();
        $todayExpenditure = $this->getTodayExpenditure();

        $userChart = $this->getUserRegister();

        // 今日收入
        // 今日支出
        // 本月总收入
        // 本月总支出
        // 总收入
        // 总支出
        // 今日新增用户

        return $content
            ->header('平台运营数据统计')
            ->description('平台运营数据统计')
            ->row(function (Row $row) use ($todayIncome, $todayExpenditure) {

                $row->column(6, function (Column $column) use ($todayIncome) {
                    $column->append(new Box('收入', $todayIncome));
                });
                $row->column(6, function (Column $column) use ($todayExpenditure) {
                    $column->append(new Box('支出', $todayExpenditure));
                });
            })
            ->row(function (Row $row) use ($userChart,$todayIncome) {
                $row->column(12, function (Column $column) use ($userChart) {
                    $column->append(new Box('近30天新用户数', $userChart));
                });
            });
    }

    // 获取今日收入（活动、企业会员费）
    public function getTodayIncome(){
        // 获取今天开始时间
        $todayDateObj = Carbon::today();
        // 获取所有今天支付过的活动
        $payActiveData = ActivityApply::whereIn('pay_status',[ActivityApply::PAY_STATUS_PAID])
            ->where(function ($query) use($todayDateObj){
                $query->where('paid_at','>=',$todayDateObj->toDateTimeString());
            })
            ->select('price','paid_at')
            ->get();


        // 获取所有今天开通并支付的企业会员
        $companyCardLog = CompanyCardLog::where('is_pay', CompanyCardLog::PAY_PAID)->where('paid_at','>=',$todayDateObj->toDateTimeString())
            ->select('user_id','paid_at', 'money')->get();

        $payActiveAmount = $payActiveData->sum('price');
        $companyCardAmount = $companyCardLog->sum('money');

        $data = [
            [
                'name' => '已支付活动',
                'value' => $payActiveAmount,
            ],
            [
                'name' => '开通企业会员',
                'value' => $companyCardAmount,
            ],
        ];

        $setSeries = [
            'name' => '金额',
            'type' => 'pie',
        ];

        $totalMoney = array_sum(array_column($data,'value'));
        $echarts = (new Echarts('今日总收入', '（'.$totalMoney.' 元）'))
            ->setData($data)

            ->setSeriesType('pie')
            ->setSeries($setSeries);
       return $echarts;
    }


    // 获取今日支出（活动退款和活动返佣结算等）
    public function getTodayExpenditure(){
        $UserBalanceLogModel = new UserBalanceLog();
        // 获取今天开始时间
        $todayDateObj = Carbon::today();

        // 活动返佣
        $activeRebate = $UserBalanceLogModel::query()->where('updated_at','>=',$todayDateObj->toDateTimeString())
            ->where('type', $UserBalanceLogModel::TYPE_ACTIVITY_REWARD)
            ->sum('money');


        // 退款金额
        $refundAmount = ActivityApply::query()->where('refund_status', ActivityApply::REFUND_STATUS_SUCCESS)
            ->where('refund_at', '>=', $todayDateObj->toDateTimeString())->sum('price');

        $data = [
            [
                'name' => '活动返佣',
                'value' => $activeRebate,
            ],
            [
                'name' => '活动退款',
                'value' => $refundAmount,
            ],
        ];

        $setSeries = [
            'name' => '金额',
            'type' => 'pie',
        ];

        $totalMoney = array_sum(array_column($data,'value'));
        $echarts = (new Echarts('今日总支出', '（'.$totalMoney.' 元）'))
            ->setData($data)
            ->setSeriesType('pie')
            ->setSeries($setSeries);

        return $echarts;
    }


    public function getUserRegister(){
        // 新用户统计（默认30天）
        $userBeginDateObj = Carbon::now()->subMonth();
        $userBeginDate = $userBeginDateObj->toDateTimeString();
        $userEndDateObj = Carbon::now();
        $userEndDate = $userEndDateObj->toDateTimeString();
        // 计算日期段内有多少天
        $userDiffDays = Carbon::now()->diffInDays($userBeginDate);
        // 保存每天日期
        $date = [];
        for($i = 0;$i < $userDiffDays;$i++){
            $date[] = $userBeginDateObj->addDays()->toDateString();
        }

        $usersData = DB::table('users')->whereBetween('created_at',[$userBeginDate,$userEndDate])
            ->selectRaw('DATE(created_at) as date,COUNT(id) as value')
            ->groupBy('date')
            ->get()->toArray();


        $data = [];
        // 循环补全日期
        foreach ($date as $key => $val){
            $data[$key] = [
                'date' => $val,
                'value' => 0
            ];
            foreach ($usersData as $item => $value){
                if($val == $value->date){
                    $data[$key] = $value;
                }
            }
        }

        $setSeries = [
            'name' => '人数',
            'type' => 'line',
        ];

        $echarts = (new Echarts('用户注册趋势','30天'))
            ->setData($data)
            ->setSeriesType('line')
            ->setSeries($setSeries)
            ->setDataZoom(true);

        return $echarts;
    }
}
