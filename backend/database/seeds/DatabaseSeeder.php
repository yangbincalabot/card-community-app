<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(UsersSeeder::class); // 创建会员测试数据
        $this->call(CompanyCardSeeder::class); // 创建企业名片测试数据
        $this->call(CarteSeeder::class); // 创建名片测试数据
        $this->call(SupplySeeder::class); // 创建供需测试数据
    }
}
