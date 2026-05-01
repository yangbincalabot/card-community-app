<?php

namespace App\Listeners;

use App\Events\UserMoneyEvent;
use App\Models\User\UserBalance;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserMoneyListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(UserMoneyEvent $event)
    {
        // 获取用户
        $user = $event->getUser();
        $user->load('balance');
        $balanceUser = $user->balance;
        if($balanceUser){
            // 用户金额修改时，修改密钥key
            $key = UserBalance::encryptKey($balanceUser->money, $balanceUser->frozen_money, $balanceUser->total_revenue);
            $user->balance()->update([
                'key' => $key
            ]);
        }else{
            UserBalance::addDefaultData($user->id);
        }

    }
}
