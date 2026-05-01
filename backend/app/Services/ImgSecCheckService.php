<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/29 0029
 * Time: 9:51
 */
namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;

class ImgSecCheckService{

//    public function imgCheck($imgPath) {
//        $access_token = $this->getAccessToken();
//        $filedata = [
//            'media'=>new \CURLFile($_SERVER['DOCUMENT_ROOT'].$imgPath)
//        ];
//        $file_data = json_encode($filedata);
//        $headers = [
//            'Content-Type' => 'application/octet-stream'
//        ];
//        $url = "https://api.weixin.qq.com/wxa/img_sec_check?access_token=".$access_token;
//        $Psr7Request = new Psr7Request('POST', $url, $headers, $file_data);
//        $client = new Client();
//        $response = $client->send($Psr7Request, ['timeout' => 10]);
//        $body = $response->getBody();
//        $json_str = mb_convert_encoding($body,"utf8","UTF-8");
//        $json_arr = json_decode($json_str,true);
//        print_r($json_arr);exit;
//        return $body;
//    }


    function imgCheck($img_path){
        $access_token = $this->getAccessToken();
        $url ='https://api.weixin.qq.com/wxa/img_sec_check?access_token='.$access_token;

        $post_data = [
            'media'=>new \CURLFile($_SERVER['DOCUMENT_ROOT'].$img_path)
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
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