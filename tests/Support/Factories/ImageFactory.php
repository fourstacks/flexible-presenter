<?php

namespace AdditionApps\FlexiblePresenter\Tests\Support\Factories;

use AdditionApps\FlexiblePresenter\Tests\Support\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImageFactory extends Factory
{
    protected $model = Image::class;

    public function definition()
    {
        return [
            'url' => 'foo',
        ];
    }
}
