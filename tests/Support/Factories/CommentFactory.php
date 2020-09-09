<?php

namespace AdditionApps\FlexiblePresenter\Tests\Support\Factories;

use AdditionApps\FlexiblePresenter\Tests\Support\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition()
    {
        return [
            'body' => 'bar',
        ];
    }
}
