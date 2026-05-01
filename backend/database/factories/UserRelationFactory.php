<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\User\UserRelation;
use Faker\Generator as Faker;
$user_ids = DB::table('users')->whereNotIn('id', range(101, 116))->pluck('id')->toArray();
$factory->define(UserRelation::class, function (Faker $faker) use (&$user_ids) {
    $user_id = array_pop($user_ids);
    return [
        'from_user_id' => 115,
        'to_user_id' => $user_id
    ];
});
