<?php

use Illuminate\Database\Seeder;
use App\Models\CompanyCard;
class CompanyCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = \App\Models\User::query()->where('id', '>', 139)->limit(30)->get();
        foreach($users as $user) {
            CompanyCard::addDefaultCompanyCard($user->id, $user->nickname, $user->avatar);
        }
    }
}
