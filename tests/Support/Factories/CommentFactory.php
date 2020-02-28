<?php

use AdditionApps\FlexiblePresenter\Tests\Support\Models\Comment;

$factory->define(Comment::class, function (Faker\Generator $faker) {
    return [
        'body' => 'bar',
    ];
});
