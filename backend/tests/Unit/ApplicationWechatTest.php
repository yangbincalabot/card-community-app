<?php

namespace Tests\Unit;

use App\Services\AssociationsService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApplicationWechatTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function testWechat(){
        $server = new AssociationsService();
        $result = $server->wechatPayNotify();
        dump($result);

        $this->assertTrue(true);
//        $server->wechatPayNotify();
    }

    public function testWechatRefund(){
        $server = new AssociationsService();
        $result = $server->wechatRefundNotify();
        dump($result);
        $this->assertTrue(true);
    }
}
