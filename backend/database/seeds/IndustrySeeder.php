<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndustrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('industries')->insert([
            ['name' => '通信/电信运营、增值服务'],
            ['name' => '通信/电信/网络设备'],
            ['name' => '网络游戏'],
            ['name' => '计算机硬件'],
            ['name' => '计算机软件'],
            ['name' => '计算机服务(系统/数据/维护)'],
            ['name' => '互联网/电子商务'],
            ['name' => '冶金冶炼|五金|采掘'],
            ['name' => '专业服务'],
            ['name' => '机械机电|自动化'],
            ['name' => 'IT|通信|互联网'],
            ['name' => '金融业'],
        ]);
    }
}
