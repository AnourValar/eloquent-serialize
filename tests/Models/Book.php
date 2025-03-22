<?php

namespace AnourValar\EloquentSerialize\Tests\Models;

class Book extends \Illuminate\Database\Eloquent\Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
