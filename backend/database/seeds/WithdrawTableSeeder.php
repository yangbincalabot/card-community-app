<?php

use Illuminate\Database\Seeder;

class WithdrawTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\Withdraw::class, 50)->create();
    }
}
