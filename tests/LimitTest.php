<?php

namespace AnourValar\EloquentSerialize\Tests;

use AnourValar\EloquentSerialize\Tests\Models\User;
use AnourValar\EloquentSerialize\Tests\Models\UserPhone;

class LimitTest extends AbstractTest
{
    /**
     * @return void
     */
    public function testSimple()
    {
        // Limit
        $this->compare(User::limit(10));

        // Limit with offset
        $this->compare(User::offset(20)->limit(10));
    }
}
