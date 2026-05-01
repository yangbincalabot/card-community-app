<?php

use Illuminate\Database\Seeder;

class CommunalTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\Communal::class, 30)->create();
    }
}
