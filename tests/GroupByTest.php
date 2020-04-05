<?php

namespace AnourValar\EloquentSerialize\Tests;

use AnourValar\EloquentSerialize\Tests\Models\User;
use AnourValar\EloquentSerialize\Tests\Models\UserPhone;

class GroupByTest extends AbstractTest
{
    /**
     * @return void
     */
    public function testSimple()
    {
        // One column
        $this->compare(User::groupBy('title'));
    }
}
