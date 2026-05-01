<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\User\UserApplyAgent;
use Faker\Generator as Faker;

$factory->define(UserApplyAgent::class, function (Faker $faker) {
    $user_ids = DB::table('users')->pluck('id')->toArray();
    unset($user_ids['108'], $user_ids['116']);
    return [
        'user_id' => $faker->randomElement($user_ids),
        'agent_id' => $faker->randomElement([1, 2, 3]),
        'name' => $faker->name,
        'mobile' => $faker->phoneNumber,
        'id_card' => $faker->uuid,
        'province' => 140000,
        'city' => 140200,
        'district' => 140202,
        'address' => $faker->address,
        'status' => $faker->randomElement([1, 2, 3])
    ];
});
