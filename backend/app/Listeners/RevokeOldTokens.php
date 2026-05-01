<?php

namespace App\Listeners;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Events\AccessTokenCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Yansongda\Pay\Log;

class RevokeOldTokens
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
     * @param  AccessTokenCreated  $event
     * @return void
     */
    public function handle(AccessTokenCreated $event)
    {
        //
        $where['user_id'] = $event->userId;
        $where['client_id'] = $event->clientId;
        $where['revoked'] = 0;
        $where[] = ['created_at','<',Carbon::now()->toDateTimeString()];
        $where[] = ['id','!=',$event->tokenId];
        DB::table('oauth_access_tokens')->where($where)->delete();
    }
}
