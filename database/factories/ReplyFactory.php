<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Conversation;
use App\Model;
use App\Reply;
use App\User;
use Faker\Generator as Faker;

$factory->define(Reply::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class)->create(),
        'conversation_id' => factory(Conversation::class)->create(),
        'body' => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),
    ];
});
