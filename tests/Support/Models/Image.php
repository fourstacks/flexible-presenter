<?php

namespace AdditionApps\FlexiblePresenter\Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $guarded = [];
    protected $dates = [
        'published_at',
    ];

    public function posts()
    {
        return $this->belongsToMany(Post::class)
            ->withPivot('test');
    }
}
