<?php

namespace Tests\Unit;

use App\Services\CompanySupplyService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanySupplyServiceTest extends TestCase
{

    public function testGetCompanySupply(){
        $service = new CompanySupplyService();;
        $companySupply = $service->getCompanySupply(0);
        $this->assertTrue(count($companySupply) === 0);

        $companySupply = $service->getCompanySupply(143);
        $this->assertTrue($companySupply->count() >= 0);
    }
}
