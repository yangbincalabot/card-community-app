<?php

namespace Tests\Unit;

use App\Facades\Sms;
use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\VerifyCodeNotification;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SmsTest extends TestCase
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

    public function testSend(){
        $smsParam = array(
            'code' => '123456',
            'content'  => '1111',
        );
        $result = Sms::send('13924755965', $smsParam);
        dump($result);
    }
}
