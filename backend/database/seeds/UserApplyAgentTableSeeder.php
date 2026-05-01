<?php

use Illuminate\Database\Seeder;

class UserApplyAgentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\User\UserApplyAgent::class, 50)->create();
    }
}
