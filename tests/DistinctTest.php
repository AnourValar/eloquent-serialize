<?php

namespace AnourValar\EloquentSerialize\Tests;

use AnourValar\EloquentSerialize\Tests\Models\User;
use AnourValar\EloquentSerialize\Tests\Models\UserPhone;

class DistinctTest extends AbstractTest
{
    /**
     * @return void
     */
    public function testSimple()
    {
        $this->compare(User::distinct('title'));
    }
}
