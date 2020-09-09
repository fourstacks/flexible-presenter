<?php

use AdditionApps\FlexiblePresenter\Tests\Support\Models\Post;
use Illuminate\Support\Carbon;

$factory->define(Post::class, function (Faker\Generator $faker) {
    return [
        'title' => 'foo',
        'body' => 'bar',
        'published_at' => Carbon::now(),
    ];
});