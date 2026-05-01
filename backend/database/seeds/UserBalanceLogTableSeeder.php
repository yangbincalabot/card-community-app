<?php

use Illuminate\Database\Seeder;

class UserBalanceLogTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\User\UserBalanceLog::class, 100)->create();
    }
}
