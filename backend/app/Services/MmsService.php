<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/8
 * Time: 16:19
 */

namespace App\Services;
use App\Models\MmsRecord;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Intervention\Image\Facades\Image;

class MmsService
{

    // 发送彩信
    public function send($carteData){
        $uid = Auth::id();
        MmsRecord::checkSendFrequency($uid);
        $appid = config('sms.graphic.ms_appid');
//        print_r($appid);exit;
        $appkey = config('sms.graphic.ms_appkey');
        $url = config('sms.graphic.ms_xsend_url_default');
        $project = config('sms.graphic.ms_project');
        $phone = $carteData['phone'];
        $vars = $this->getVars($carteData);
//        $data = "appid=$appid&to=$phone&project=$project&signature=$appkey";
        $data = "appid=$appid&to=$phone&project=$project&vars=$vars&signature=$appkey";
//        print_r($data);exit;
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $request = new Request('POST', $url, $headers, $data);
        $client = new Client();
        $response = $client->send($request, ['timeout' => 10]);
        $body = $response->getBody();
        $content = json_decode($body->getContents(), true);
//        print_r($content);exit;
        if(isset($content['status']) && $content['status'] == 'success'){
            MmsRecord::addRecord($uid, $phone, $content['send_id']);
            return true;
        } else {
            abort(403, '发送彩信失败');
        }

    }


    public function getVars($carteData) {
//        $text['code'] = '请打开手机微信扫码进入小程序，完善名片信息';
        $text['code'] = '请尽快完善名片信息';
        $data = json_encode($text, true);
        return $data;
    }


    // 生成图片测试
    public function generateImage($data = array()) {
        $img = Image::make(public_path('storage/images/generate/templateImage.png'));

        $filePathDir = public_path('storage/images/generate/').date('Y').'/'.date('m').'/';
        if (!is_dir($filePathDir)) {
            mkdir($filePathDir, 0777, true);
        }
        $data = array(
            'company_name' => '深蓝互联',
            'name' => '小曾',
            'position' => 'php开发工程师',
            'phone' => '13477042412',
            'email' => '1263198140@qq.com',
            'address_title' => '宝龙大厦',
        );
        //在底图上添加文字
        $company = $data['company_name'];
        $name = $data['name'];
        $position = $data['position'];
        $phone = $data['phone'];
        $email = $data['email'];
        $address_title = $data['address_title'];
        if ($company) {
            $img->text($company, 20, 30, function ($font) {
                $font->file(public_path('fonts/generate.TTF'));  //字体
                $font->size(16);   //大小
                $font->color('#ffffff');  //颜色
            });
        }
        if ($name) {
            $img->text($name, 20, 65, function ($font) {
                $font->file(public_path('fonts/generate.TTF'));  //字体
                $font->size(16);   //大小
                $font->color('#ffffff');  //颜色
            });
        }
        if ($position) {
            $img->text($position, 70, 65, function ($font) {
                $font->file(public_path('fonts/generate.TTF'));  //字体
                $font->size(14);   //大小
                $font->color('#ffffff');  //颜色
            });
        }
        if ($phone) {
            $img->text($phone, 45, 100, function ($font) {
                $font->file(public_path('fonts/generate.TTF'));
                $font->size(14);
                $font->color('#ffffff');
            });
        }
        if ($email) {
            $img->text($email, 45, 135, function ($font) {
                $font->file(public_path('fonts/generate.TTF'));
                $font->size(14);
                $font->color('#ffffff');
            });
        }
        if ($address_title) {
            $img->text($address_title, 45, 170, function ($font) {
                $font->file(public_path('fonts/generate.TTF'));
                $font->size(14);
                $font->color('#ffffff');
            });
        }
        $imgUrl = $filePathDir.time().rand(1000, 9999).'.png';
        $img->save($imgUrl);
        return $imgUrl;
    }

}