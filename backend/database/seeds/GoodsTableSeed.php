<?php

use Illuminate\Database\Seeder;

class GoodsTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\Goods::class, 20)->create();
    }
}
