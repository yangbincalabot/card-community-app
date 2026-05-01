<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity\Activity;
use App\Models\Carte;
use App\Models\User\UserAuth;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/*
 *  订阅消息
 */
class SubNewsController extends Controller{

    const TEMPLATE_ID_ONE = "v29Th3pRjBkWS5XS-nyZzAiyWK76qR_NroHJSqLKcb4"; // 活动开始提醒
//    const TEMPLATE_ID_TWO = "TIdNo-MGFX4bJGineTjdctm3lPrA-e9rLVkoPGP44fg"; // 完善信息提醒
    const TEMPLATE_ID_TWO = "jCItW95uAINhEOimxH3l9xeV_3KRuhqFjMdNexzSILA"; // 完善信息提醒

    public function sendActivitySubMsg(Request $request) {
        $id = $request->input('id', '');
        $activityData = Activity::where('id', $id)->select(['id', 'activity_time', 'apply_end_time', 'title', 'address_title', 'type'])->first();
        if (empty($id) || empty($activityData)) {
            return ;
        }
        $type = $activityData->type;
        $touser = $this->getOpenid();
        $template_id = self::TEMPLATE_ID_ONE;
        $page = ($type == 1) ? "pages/activity/detail/index":"pages/activity/meetingDetail/index";
        $dataArr = [];
        $dataArr['touser'] = $touser;
        $dataArr['template_id'] = $template_id;
        $dataArr['page'] = $page;
        $dataArr['data']['time3'] = ['value' => $activityData->activity_time];
        $dataArr['data']['thing2'] = ['value' => $activityData->title];
        $dataArr['data']['thing4'] = ['value' => $activityData->address_title];
        $body = $this->sendReally($dataArr);
        return $body;
    }

    public function sendPerfectSubMsg(Request $request) {
        $id = $request->input('id', '');
        $carteInfo = Carte::query()->where('id', $id)->first();
        if (empty($id) || empty($carteInfo)) {
            return ;
        }
        $user = $request->user();
        $touser = $this->getOpenid();
        $template_id = self::TEMPLATE_ID_TWO;
        $dataArr = [];
        $dataArr['touser'] = $touser;
        $dataArr['template_id'] = $template_id;
        $dataArr['page'] = 'pages/my/card/editCard/index';
        $time = Carbon::parse($user->created_at)->toDateTimeString();
        $name = $user->nickname ?:'未设置';
        $dataArr['data']['date1'] = ['value' => $time];
        $dataArr['data']['name2'] = ['value' => $name];
        $dataArr['data']['name3'] = ['value' => $carteInfo->name];
        $dataArr['data']['thing4'] = ['value' => $carteInfo->company_name];
        $dataArr['data']['phone_number5'] = ['value' => $carteInfo->phone];
        $body = $this->sendReally($dataArr);
        return $body;
    }

    protected function sendReally($dataArr) {
        $access_token = $this->getAccessToken();
        $data = json_encode($dataArr);
        $headers = [
            'Content-Type' => 'application/json; charset=UTF-8'
        ];
        $url = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=".$access_token;
        $Psr7Request = new Psr7Request('POST', $url, $headers, $data);
        $client = new Client();
        $response = $client->send($Psr7Request, ['timeout' => 10]);
        $body = $response->getBody();
        return $body;
    }

    protected function getOpenid() {
        $openid = UserAuth::where('user_id', Auth::id())->value('identifier');
        return $openid;
    }

    protected function getAccessToken()
    {
        $access_token = Cache::get('ACCESS_TOKEN');
        if(!$access_token){
            $url = sprintf('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s',
                env('WECHAT_MINI_PROGRAM_APPID'), env('WECHAT_MINI_PROGRAM_SECRET'));
            $responseData = json_decode($this->request($url), true);
            if(!isset($responseData['access_token'])){
                abort(503, '请求错误');
            }
            // 设置缓存，官方默认缓存7200秒
            Cache::put('ACCESS_TOKEN', $responseData['access_token'], Carbon::now()->addSeconds($responseData['expires_in'] / 60));
            $access_token = $responseData['access_token'];
        }
        return $access_token;
    }

    public function request($url, $data = array()){
        if(!empty($data)){
            $data = json_encode($data);
            $context_options = array (
                'http' => array (
                    'method' => 'POST',
                    'header'=> "Content-type: application/json;charset=utf-8\r\n"
                        . "Content-Length: " . strlen($data) . "\r\n",
                    'content' => $data
                )
            );
        }else{
            $context_options = array (
                'http' => array (
                    'method' => 'GET',
                    'header'=> "Content-type: application/json;charset=utf-8\r\n"
                )
            );
        }

        $context = stream_context_create($context_options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }
}
