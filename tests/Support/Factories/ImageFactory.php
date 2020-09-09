<?php

use AdditionApps\FlexiblePresenter\Tests\Support\Models\Image;

$factory->define(Image::class, function (Faker\Generator $faker) {
    return [
        'url' => 'foo',
    ];
});
