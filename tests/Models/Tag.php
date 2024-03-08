<?php

namespace AnourValar\EloquentSerialize\Tests\Models;

class Tag extends \Illuminate\Database\Eloquent\Model
{
    public function taggable()
    {
        return $this->morphTo();
    }
}
