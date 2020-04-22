<?php

namespace AnourValar\EloquentSerialize\Tests\Models;

class User extends \Illuminate\Database\Eloquent\Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userPhones()
    {
        return $this->hasMany(UserPhone::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function userPhoneNote()
    {
        return $this->hasManyThrough(UserPhoneNote::class, UserPhone::class);
    }
}
