<?php

namespace AnourValar\EloquentSerialize\Tests\Models;

class User extends \Illuminate\Database\Eloquent\Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    public function userPhones()
    {
        return $this->hasMany(UserPhone::class);
    }
}
