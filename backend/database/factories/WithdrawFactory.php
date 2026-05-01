<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Withdraw;
use Faker\Generator as Faker;

$factory->define(Withdraw::class, function (Faker $faker) {
    return [
        'user_id' => 115,
        'bank_id' => 2,
        'card_name' => $faker->name,
        'card_number' => $faker->randomNumber(),
        'money' => $faker->randomDigit,
        'status' => $faker->randomElement(array_keys(Withdraw::getStatus())),
        'remark' => $faker->text(100)
    ];
});
