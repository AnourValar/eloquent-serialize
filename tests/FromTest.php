<?php

namespace AnourValar\EloquentSerialize\Tests;

use AnourValar\EloquentSerialize\Tests\Models\User;
use AnourValar\EloquentSerialize\Tests\Models\UserPhone;
use AnourValar\EloquentSerialize\Tests\Models\Post;

class FromTest extends AbstractTest
{
    /**
     * @return void
     */
    public function testSimple()
    {
        $this->compare(
            User::whereExists(function ($query)
            {
                $query
                    ->from('user_phones')
                    ->whereRaw('user_phones.user_id = users.id');
            })
        );
    }
}
