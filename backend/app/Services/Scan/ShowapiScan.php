<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/8
 * Time: 18:08
 */

namespace App\Services\Scan;
use App\Models\Area;
use GuzzleHttp\Client;


// 万维易源名片接口
class ShowapiScan implements ScanInterface
{
    public function resolve($file)
    {
        $app_id = config('card.gateways.showapi_scan.app_id');
        $app_access = config('card.gateways.showapi_scan.app_access');
        $api_url = config('card.gateways.showapi_scan.api_url');
        $data = file_get_contents($file);
        $data = base64_encode($data); // base64


        $client = new Client();
        $response = $client->request('POST', $api_url, [
            'form_params' => [
                'showapi_appid' => $app_id,
                'showapi_sign' => $app_access,
                'imgData' => $data
            ]
        ]);
//        //返回信息包体
        $body = $response->getBody();

        $content = json_decode($body->getContents(), true);
        if(isset($content['showapi_res_code']) && $content['showapi_res_code'] == 0){
            $result = $content['showapi_res_body']['result'];
            $resolveData = [];
            foreach($result as $item) {
                $resolveData[$item['paraSimpleName']] = $item['paraVal'];
            }
            $address = $resolveData['dz'];
            $addressInfo = Area::getAddressInfo($address);
            // 检测输入文本是否合法
            $secMsg = $resolveData['xm'] . $resolveData['gs'] . $resolveData['zw/bm'] . $address;
            if (!msgSecCheck($secMsg)) {
                abort(403, '解析内容含有不合法的词汇，解析失败');
            }
            return [
                'name' => $resolveData['xm'], // 真名
                'company_name' => $resolveData['gs'], // 公司名
                'position' => $resolveData['zw/bm'], // 职务
                'phone' => $resolveData['sj'], // 手机
                'email' => $resolveData['dzyx'], // 邮箱
                'address_title' => $address, // 地址
                'province' => $addressInfo['province'],
                'city' => $addressInfo['city'],
                'longitude' => $addressInfo['longitude'],
                'latitude' => $addressInfo['latitude']
            ];
        }else{
            abort(500, $content['showapi_res_error']);
        }
    }
}