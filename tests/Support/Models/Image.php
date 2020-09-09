<?php

namespace AdditionApps\FlexiblePresenter\Tests\Support\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

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
