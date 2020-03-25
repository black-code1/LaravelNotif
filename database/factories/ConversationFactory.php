<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Conversation;
use App\User;
use Faker\Generator as Faker;

$factory->define(Conversation::class, function (Faker $faker) {
    return [
        //
        'user_id' => factory(User::class)->create(),
        'title' => $faker->title,
        'body' => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),
    ];
});
