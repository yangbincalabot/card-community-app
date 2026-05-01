<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/4/10 0010
 * Time: 17:16
 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MmsRecord;
use App\Services\MmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MmsController extends Controller
{
    public function sendMms($data = array()) {
//        print_r(Auth::id());exit;
        if (Auth::id() != 277) {
            return false;
        }
        $mmsService = new MmsService();
        $carteData = array(
            'company_name' => '深蓝互联',
            'name' => '小曾',
            'position' => 'php开发工程师',
            'phone' => '13477042412',
            'email' => '1263198140@qq.com',
            'address_title' => '宝龙大厦',
        );
        $result = $mmsService->send($carteData);
        print_r($result);
    }


    /*
     * {
        "events":"request",
        "address":"138xxxxxxx",
        "send_id":"093c0a7df143c087d6cba9cdf0cf3738",
        "app":xxxxx,
        "timestamp":1415014855,
        "token":"067ef7e2f286a9a56eabb07dc9657852",
        "signature":"a70d09a9345adfdd353d34a505dac4ca"
        }
     */
    public function sendNotify(Request $request) {
        $response = $request->all();
        $data = $response;
        // delivered == success | dropped ==  fail
        if (!$data['events'] || !in_array($data['events'], ['delivered', 'dropped'])) {
            return '';
        }
        $status = $data['events'] == 'delivered' ? MmsRecord::STATUS_ONE:MmsRecord::STATUS_TWO;
        $send_id = $data['send_id'] ?? '';
        if (empty($send_id)) {
            return '';
        }
        $info = MmsRecord::query()->where('send_id', $send_id)->whereNull('re_at')->first();
        if (empty($info)) {
            return '';
        }
        $info->status = $status;
        if ($status == MmsRecord::STATUS_TWO) {
            $info->report = $data['report'] ?? '';
        }
        $info->re_at = Carbon::now();
        $info->save();
        return '';

    }

}
