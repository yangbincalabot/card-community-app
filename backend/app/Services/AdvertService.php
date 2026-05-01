<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 19:39
 */

namespace App\Services;


use App\Models\AdvPosition;

class AdvertService
{
    public function get($adv_position_name){
        $query = AdvPosition::query();
        if(strpos($adv_position_name, '|') !== false){
            $query->whereIn('flag', explode('|', $adv_position_name));
        }else{
            $query->where('flag', $adv_position_name);
        }
        $adv_positions = $query->with([
                'advs' => function($query){
                    $query->orderBy('sort', 'DESC')->orderBy('id', 'DESC');
                }
            ])->get();

        return $adv_positions;

    }
}