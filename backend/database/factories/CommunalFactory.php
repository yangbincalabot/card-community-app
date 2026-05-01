<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Communal;
use Faker\Generator as Faker;

$factory->define(Communal::class, function (Faker $faker) {
    return [
        'title' => $faker->catchPhrase,
        'content' => sprintf("<p>%s</p>", str_repeat($faker->catchPhrase, 5)),
        'image' => 'images/263f236307335b864d1921a2b7f9a554.png'
    ];
});
