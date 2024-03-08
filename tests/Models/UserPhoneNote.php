<?php

namespace AnourValar\EloquentSerialize\Tests\Models;

class UserPhoneNote extends \Illuminate\Database\Eloquent\Model
{
    public function userPhone()
    {
        return $this->belongsTo(UserPhone::class);
    }
}
