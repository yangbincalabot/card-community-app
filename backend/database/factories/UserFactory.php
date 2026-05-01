<?php

use App\Models\User;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    $type = $faker->randomElement([1,2]);
    $enterprise_at = null;
    if($type == 2){
        $enterprise_at = \Carbon\Carbon::now()->addYears(1);
    }
    return [
        'nickname' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'avatar' => $faker->imageUrl(),
        'type' => $type,
        'phone' => $faker->phoneNumber,
        'enterprise_at' => $enterprise_at
    ];
});
