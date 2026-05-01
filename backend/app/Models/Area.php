<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'china_area';

    const PROVINCE = 1;

    public function parent(){
        return $this->belongsTo(Area::class, 'parent_id');
    }

    public function children(){
        return $this->hasMany(self::class, 'parent_id');
    }

    // 获取名称获取省份
    public static function getProvince($name){
        $areas = self::where('name', 'like', '%' . $name . '%')->with('parent')->get();
        $province_name = '';
        foreach($areas as $area){
            if($area->parent_id == 1){
                // 省
                $province_name = $area->name;
                break;
            }elseif($area->parent && $area->parent->parent_id == 1){
                // 市
                $province_name = $area->parent->name;
                break;
            }else{
                $parent = $area->parent->parent ?? '';
                if(empty($parent)){
                    return '';
                }
                if($parent && $parent->parent_id == 1){
                    // 区
                    $province_name = $parent->name;
                    break;
                }else{
                    // 县
                    $province_name = $parent->parent->name ?? '';
                    break;
                }
            }
        }
        return $province_name;
    }

    // 根据地址获取省市区、经度和纬度
    public static function getAddressInfo($address){
        $ak = env('BAIDU_MAP_AK');
        $addressInfo = [
            'longitude' => '',
            'latitude' => '',
            'province' => '',
            'city' => ''
        ];
        $params = [
            'ak' => $ak,
            'output' => 'json',
            'address' => $address,
        ];
        $url = sprintf("%s?%s", 'http://api.map.baidu.com/geocoding/v3/', http_build_query($params));
        $response = json_decode(file_get_contents($url), true);
        if($response['status'] === 0 && isset($response['result'])){
            $lng = $response['result']['location']['lng'];
            $lat = $response['result']['location']['lat'];
            $params = [
                'location' => sprintf('%s,%s', $lat, $lng),
                'output' => 'json',
                'ak' => $ak
            ];
            $url = sprintf("%s?%s", 'http://api.map.baidu.com/reverse_geocoding/v3/', http_build_query($params));
            $response = json_decode(file_get_contents($url), true);
            if($response['status'] === 0 && isset($response['result'])){
                $result = $response['result'];
                $addressInfo['longitude'] = $result['location']['lng'];
                $addressInfo['latitude'] = $result['location']['lat'];
                $addressInfo['province'] = $result['addressComponent']['province'];
                $addressInfo['city'] = $result['addressComponent']['city'];
            }

        }
        return $addressInfo;
    }
}
