<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */


use Faker\Generator as Faker;

$factory->define(\App\Models\User\UserBalanceLog::class, function (Faker $faker) {
    $log_types = [1,3,4];
    $log_type_texts = [
        1 => '%s 拿货%.2f元',
        3 => '%s 拿货%.2f元',
        4 => '%s 退款%.2f元',
    ];
    $log_type = $faker->randomElement($log_types);
    $money = $faker->randomFloat(2, 10, 1000);
    return [
        'user_id' => 115,
        'log_type' => $log_type,
        'type' => $faker->randomElement([1, 2, 3, 4]),
        'money' => $money,
        'remark' => sprintf($log_type_texts[$log_type], $faker->name, $money)
    ];
});
