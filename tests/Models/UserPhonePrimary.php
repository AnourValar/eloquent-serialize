<?php

namespace AnourValar\EloquentSerialize\Tests\Models;

use AnourValar\EloquentSerialize\Tests\Scopes\PrimaryScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;

#[ScopedBy([PrimaryScope::class])]
class UserPhonePrimary extends \Illuminate\Database\Eloquent\Model
{
    public $table = 'user_phones';
}
