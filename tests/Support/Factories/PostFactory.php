<?php

namespace AdditionApps\FlexiblePresenter\Tests\Support\Factories;

use AdditionApps\FlexiblePresenter\Tests\Support\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        return [
            'title' => 'foo',
            'body' => 'bar',
            'published_at' => Carbon::now(),
        ];
    }
}