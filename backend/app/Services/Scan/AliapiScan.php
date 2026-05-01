<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/9
 * Time: 9:46
 */

namespace App\Services\Scan;
use App\Models\Area;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

// 阿里云api市场接口
class AliapiScan implements ScanInterface
{
    public function resolve($file)
    {
        $data = file_get_contents($file);
        $data = base64_encode($data); // base64
        $api_url = config('card.gateways.aliapi_scan.api_url');
        $app_code = config('card.gateways.aliapi_scan.app_code');

        $headers = [
            'Authorization' => sprintf('APPCODE %s', $app_code),
            'Content-Type' => 'application/json; charset=UTF-8'
        ];
        $requestBody = json_encode(['image' => $data]);
        $request = new Request('POST', $api_url, $headers, $requestBody);
        $client = new Client();
        $response = $client->send($request, ['timeout' => 10]);
        $body = $response->getBody();
        $content = json_decode($body->getContents(), true);
        if(isset($content['success']) && $content['success'] === true){
            $address = '';
            if(isset($content['addr'][0])){
                $address = array_pop($content['addr']);
                if(!empty($content['addr'])){
                    $address .= array_pop($content['addr']);
                }
            }
            $addressInfo = Area::getAddressInfo($address);
            // 检测输入文本是否合法
            $secMsg = $content['name']  . $address;
            if (!msgSecCheck($secMsg)) {
                abort(403, '解析内容含有不合法的词汇，解析失败');
            }
            return [
                'name' => $content['name'], // 真名
                'company_name' => $content['company'][0] ?? '', // 公司名
                'position' => $content['title'][0] ?? '', // 职务
                'phone' => $content['tel_cell'][0] ?? '', // 手机
                'email' => $content['email'][0] ?? '', // 邮箱
                'address_title' => $address, // 地址
                'province' => $addressInfo['province'],
                'city' => $addressInfo['city'],
                'longitude' => $addressInfo['longitude'],
                'latitude' => $addressInfo['latitude']
            ];
        }else{
            abort(500, '解析失败，请重拍');
        }

    }
}
