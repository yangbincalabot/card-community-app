<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/8
 * Time: 16:19
 */

namespace App\Services;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Intervention\Image\Facades\Image;

class SmsService
{

    public function send($carteData){
        $appId = config('sms.graphic.pt_appId');
        $appKey = config('sms.graphic.pt_appKey');
        $mmsId = config('sms.graphic.pt_mmsId');
        $url = config('sms.graphic.pt_url_send');
        $data['appId'] = $appId;
        $data['appKey'] = $appKey;
        $data['mmsId'] = $mmsId;
        $bodys = $this->getBodysData($carteData);
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8'
        ];
        $request = new Request('POST', $url, $headers, $bodys);
        $client = new Client();
        $response = $client->send($request, ['timeout' => 10]);
        $body = $response->getBody();
        $content = json_decode($body->getContents(), true);
        if(isset($content['success']) && $content['success'] === true){
            return true;
        } else {
            abort(403, '发送彩信失败');
        }

    }

//    public function getQuerysData($phone, $title='名片完善信息') {
//        $querys = "phone=".$phone."&sendTime=&title=".$title;
//        return $querys;
//    }

    //播放时间,文件类型|文件内容,文件类型|文件内容;播放时间,文件类型|文件内容,文件类型|文件内容;
    //5,jpg|4AAQSkZJR...gABAgA,txt|中昱维信双十一大促;3,gif|ExghIhcXG...RcXIiQdIB,txt|欢迎莅临采购。
    // 其中图片、视频、音频需转为base64编码进行传输，文本需gb2312字符集存储，转为base64编码后再进行传输。
    public function getBodysData($carteData) {
        $imgUrl = $this->generateImage($carteData);
        $text = $this->getText();
        $bodysStr = "3,jpg|".$imgUrl.",txt|".$text;
        return $bodysStr;
    }

    public function getText() {
        header("content-type:text/html;charset=utf8");
        $str = "请打开微信扫码，进入小程序完善名片";
        $str = iconv('UTF-8','GB2312//IGNORE',$str);
        $lastStr = base64_encode($str);
        return $lastStr;
    }

    // 生成图片测试
    public function generateImage($data = array()) {
        $img = Image::make(public_path('storage/images/generate/templateImage.jpg'));

        $filePathDir = public_path('storage/images/generate/').date('Y').'/'.date('m').'/';
        if (!is_dir($filePathDir)) {
            mkdir($filePathDir, 0777, true);
        }
        $ttf = public_path('fonts/PingFang_Medium.ttf');
        //在底图上添加文字
        $company = $data['company_name'];
        $name = $data['name'];
        $position = $data['position'];
        $phone = $data['phone'];
        $email = $data['email'];
        $address_title = $data['address_title'];
        if ($company) {
            $img->text($company, 20, 30, function ($font) use ($ttf) {
                $font->file($ttf);  //字体
                $font->size(16);   //大小
                $font->color('#ffffff');  //颜色
            });
        }
        if ($name) {
            $img->text($name, 20, 65, function ($font) use ($ttf) {
                $font->file($ttf);  //字体
                $font->size(16);   //大小
                $font->color('#ffffff');  //颜色
            });
        }
        if ($position) {
            $img->text($position, 70, 65, function ($font) use ($ttf) {
                $font->file($ttf);  //字体
                $font->size(14);   //大小
                $font->color('#ffffff');  //颜色
            });
        }
        if ($phone) {
            $img->text($phone, 45, 100, function ($font) use ($ttf) {
                $font->file($ttf);
                $font->size(14);
                $font->color('#ffffff');
            });
        }
        if ($email) {
            $img->text($email, 45, 135, function ($font) use ($ttf) {
                $font->file($ttf);
                $font->size(14);
                $font->color('#ffffff');
            });
        }
        if ($address_title) {
            $img->text($address_title, 45, 170, function ($font) use ($ttf) {
                $font->file($ttf);
                $font->size(14);
                $font->color('#ffffff');
            });
        }
        $imgUrl = $filePathDir.time().rand(1000, 9999).'.jpg';
        $img->save($imgUrl);
        return base64_encode($imgUrl);
    }

}