<?php

namespace AnourValar\EloquentSerialize\Tests;

use AnourValar\EloquentSerialize\Tests\Models\User;
use AnourValar\EloquentSerialize\Tests\Models\UserPhone;

class WithCastsTest extends AbstractTest
{
    /**
     * {@inheritDoc}
     * @see \AnourValar\EloquentSerialize\Tests\AbstractTest::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (! method_exists(User::class, 'mergeCasts')) {
            $this->markTestSkipped('Old version.');
        }
    }

    /**
     * @return void
     */
    public function testSimple()
    {
        $this->compare(User::withCasts(['user_id' => 'integer']));
    }

    /**
     * @return void
     */
    function testSelectRaw()
    {
        $this->compare(
            User::select([
                'users.*',
                'last_phone_created_at' => UserPhone::selectRaw('MAX(created_at)')->whereColumn('user_id', 'users.id')
            ])->withCasts([
                'last_phone_created_at' => 'datetime'
            ])
        );
    }
}
