<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\UserQrcodeResponse;
use App\Models\Configure;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class UserQrcodeController extends Controller
{
    protected $dir_name = 'qrcode/';
    protected $configInfo;

    public function __construct()
    {
        $this->configInfo = Configure::query()->pluck('value', 'name')->toArray();
    }

    public function get(Request $request){
        $qrcode = $request->user()->qrcode;
        if(!$qrcode){
            $access_token = $this->getAccessToken();
            $qrcode = $this->getQrcodeImage($access_token, $request->user()->id);
            $request->user()->qrcode = $qrcode;
            $request->user()->save();
        }
        return new UserQrcodeResponse(['qrcode' => Storage::disk('public')->url($qrcode)]);
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


    protected function getQrcodeImage($access_token, $user_id){
        $url = sprintf("https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=%s", $access_token); // 获取小程序二维码地址
        $requestData = [
            'scene' => 'user_id@' . $user_id, // 绑定用户id参数
        ];

        // 如果正式上线，添加page参数
        if($this->configInfo['IS_AUDIT'] == Configure::IS_AUDIT_NO){
            $requestData['page'] = 'pages/spread/index/index';
        }

        $response = $this->request($url, $requestData);
        if(strpos('errcode', $response) !== false){
            abort(503, '请求图片失败');
        }
        $filename = time() . mt_rand(100, 999) . '.png';
        Storage::disk('public')->put($this->dir_name . $filename, $response);
        return $this->dir_name . $filename;
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
