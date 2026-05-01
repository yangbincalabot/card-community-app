<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
class CarteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = \App\Models\User::query()->where('id', '>', 139)->get();
        $companyCards = \App\Models\CompanyCard::query()->get()->toArray();
        $industries = \App\Models\Industry::query()->get()->toArray();
        foreach($users as $user) {
            if(!$user->carte){
                $companyCard = Arr::random($companyCards);
                $industry = Arr::random($industries);
                $user->carte()->create([
                    'cid' =>$companyCard['id'],
                    'name' => $user->nickname,
                    'company_name' => $user->nickname . '有限公司',
                    'avatar' => $user->avatar,
                    'phone' => $user->phone,
                    'wechat' => $user->nickname,
                    'email' => $user->email,
                    'industry_id' => $industry['id'],
                    'position' => '程序员-' . $user->id,
                    'images' => [$user->avatar],
                ]);
            }
        }
    }
}
