<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

// 自定义验证规则
class CustomValidationProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // 验证手机号码
        Validator::extend('mobile', function($attribute, $value, $parameters) {
            return match_mobile($value);
        });


        //自定义电话号码验证规则改写
        Validator::extend('check_phone', function($attribute, $value, $parameters) {
            $phone = trim($value,'-');
            return match_phone($phone);
        });

        // 验证官网地址知否合法
        Validator::extend('website', function ($attribute, $value, $parameters){
            $websitePattern = '/^(?=^.{3,255}$)(http(s)?:\/\/)?(www\.)?[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+(:\d+)*(\/\w+\.\w+)*$/';
            return preg_match($websitePattern, $value) ? true : false;
        });
    }
}
