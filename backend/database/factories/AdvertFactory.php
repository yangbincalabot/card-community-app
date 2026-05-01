<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Advert;
use Faker\Generator as Faker;

$factory->define(Advert::class, function (Faker $faker) {
    $advPosition = DB::table('adv_positions')->first();
    return [
        'adv_positions_id' => $advPosition->id,
        'title' => $faker->title,
        'images' => 'images/01cb7110ae3ff24812ce9e17c8bc0dd4.png',
        'url' => '',
        'sort' => 0
    ];
});
