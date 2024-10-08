<?php

namespace AnourValar\EloquentSerialize\Tests;

use AnourValar\EloquentSerialize\Tests\Models\User;
use AnourValar\EloquentSerialize\Tests\Models\UserPhone;

class ScopeTest extends AbstractSuite
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

    /**
     * @return void
     */
    public function testWithParams()
    {
        // Primary
        $this->compare(UserPhone::major(true));

        // NOT primary
        $this->compare(UserPhone::major(false));

        // Combine
        $this->compare(UserPhone::major(false)->search('906'));
    }

    /**
     * @return void
     */
    public function testGlobal()
    {
        $query = User::withGlobalScope('foo', fn ($builder) => $builder->where('id', '<', 20));

        /** Global scopes - are the part of statically description of the model */
        $this->assertNotSame(
            $query->toSql(),
            $this->service->unserialize($this->service->serialize($query))->toSql()
        );
    }
}
