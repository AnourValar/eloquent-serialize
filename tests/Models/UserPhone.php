<?php

namespace AnourValar\EloquentSerialize\Tests\Models;

class UserPhone extends \Illuminate\Database\Eloquent\Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
