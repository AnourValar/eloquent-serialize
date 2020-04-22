<?php

namespace AnourValar\EloquentSerialize\Tests\Models;

class UserPhone extends \Illuminate\Database\Eloquent\Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function userPhoneNote()
    {
        return $this->hasOne(UserPhoneNote::class);
    }
}
