<?php

use Illuminate\Database\Seeder;

class CarteVisitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // user_id=139, carte_id = 139
        $user_ids = \App\Models\User::query()->where('id', '<>', 139)->pluck("id")->toArray();
        $carteVisits = [];
        foreach($user_ids as $user_id) {
            $carteVisits[] = [
                'user_id' => $user_id,
                'carte_id' => 139,
                'last_view_time' => \Carbon\Carbon::now()
            ];
        }
        \App\Models\CarteVisits::query()->insert($carteVisits);
    }
}
