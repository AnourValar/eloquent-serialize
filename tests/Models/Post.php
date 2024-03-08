<?php

namespace AnourValar\EloquentSerialize\Tests\Models;

class Post extends \Illuminate\Database\Eloquent\Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tag()
    {
        return $this->morphOne(Tag::class, 'taggable');
    }
}
