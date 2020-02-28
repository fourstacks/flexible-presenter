<?php

use Illuminate\Support\Carbon;
use AdditionApps\FlexiblePresenter\Tests\Support\Models\Post;

$factory->define(Post::class, function (Faker\Generator $faker) {
    return [
        'title' => 'foo',
        'body' => 'bar',
        'published_at' => Carbon::now()
    ];
});
