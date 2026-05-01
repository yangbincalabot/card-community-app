<?php

use Illuminate\Database\Seeder;

class UserRelationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\User\UserRelation::class, 50)->create();
    }
}
