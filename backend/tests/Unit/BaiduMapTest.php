<?php

namespace Tests\Unit;

use App\Models\Area;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BaiduMapTest extends TestCase
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

    public function testAddressInfo(){
        $addressInfo = Area::getAddressInfo('广东省深圳市龙华区观澜街道桂花路108号');
        dump($addressInfo);
        $this->assertTrue(!empty($addressInfo));
    }
}
