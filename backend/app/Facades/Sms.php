<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/16
 * Time: 17:19
 */

namespace App\Facades;
use Illuminate\Support\Facades\Facade;

class Sms extends Facade{
    protected static function getFacadeAccessor(){
        return 'Sms';
    }
}