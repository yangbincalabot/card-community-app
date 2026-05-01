<?php

use Illuminate\Database\Seeder;

class SupplySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companyCard = \App\Models\CompanyCard::query()->where('uid', 139)->first();
        if($companyCard){
            $cartes = $companyCard->cartes;
            if($cartes){
                $carteUserIds = $cartes->map(function($carte){
                    return $carte->uid;
                })->toArray();
                foreach ($carteUserIds as $userId){
                    // 每个用户各创建30条供需
                    for($i = 0; $i < 30; $i++){
                        \App\Models\Supply::query()->create([
                            'uid' => $userId,
                            'type' => \Illuminate\Support\Arr::random([6, 8]),
                            'content' => '供需测试内容' . $i,
                            'images' => ['https://s.cn.bing.net/th?id=ODL.3fed24b87d2e6797eb3080ed4c68f699&w=197&h=112&c=7&rs=1&qlt=80&pid=RichNav'],
                        ]);
                    }
                }
            }
        }
    }
}
