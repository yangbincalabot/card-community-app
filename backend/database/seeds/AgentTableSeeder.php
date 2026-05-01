<?php

use Illuminate\Database\Seeder;

class AgentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('agents')->insert([
            [
                'name' => '店中店',
                'price' => '9800.00',
                'introduce' => ''
            ],
            [
                'name' => '区域代理商',
                'price' => '29800.00',
                'introduce' => ''
            ],
            [
                'name' => '区域总代理',
                'price' => '1000000.00',
                'introduce' => ''
            ],
        ]);
    }
}
