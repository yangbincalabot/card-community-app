<?php

namespace App\Providers;

use App\Channels\SmsChannel;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;
use App\Gateways\AliyunGlobeGateway;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Sms', function ($app){
            $easySms = new \Overtrue\EasySms\EasySms($app->config['sms']);
            return $easySms;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
