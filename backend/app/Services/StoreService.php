<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/23
 * Time: 20:15
 */

namespace App\Services;
use App\Models\Store;
use DB;

class StoreService
{
    /**
     * @param $condition '查询条件'
     * @param int $pageSize
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getLatelyStore($formData = null, $pageSize = 15){
        $query = Store::query();
        $longitude = $formData['longitude'];
        $latitude = $formData['latitude'];

        $appends = compact('longitude', 'latitude');


        // 按门店名搜索
        if(isset($formData['name']) && $formData['name']){
            $query->where('name', 'like', '%'. $formData['name'] .'%');
            $appends['name'] = $formData['name'];
        }

        $stores = $query->select(DB::raw('*, ROUND((ACOS(SIN(('. $latitude .' * 3.1415) / 180 ) *SIN((latitude* 3.1415) / 180 ) +
COS(('. $latitude .'  * 3.1415) / 180 ) * COS((latitude* 3.1415) / 180 ) *COS(('. $longitude .' * 3.1415) / 180 
- (longitude* 3.1415) / 180 ) ) * 6380),2) as distance'))->orderBy('distance')
            ->with('user')->paginate($pageSize)->appends($appends);
        return $stores;

    }
}