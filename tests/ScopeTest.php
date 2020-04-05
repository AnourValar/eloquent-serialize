<?php

namespace AnourValar\EloquentSerialize\Tests;

use AnourValar\EloquentSerialize\Tests\Models\User;
use AnourValar\EloquentSerialize\Tests\Models\UserPhone;

class ScopeTest extends AbstractTest
{
    /**
     * @return void
     */
    public function testSimple()
    {
        // One way
        $this->compare(User::withTrashed());

        // Reverted
        $this->compare(User::withTrashed()->withoutTrashed());
    }
}
