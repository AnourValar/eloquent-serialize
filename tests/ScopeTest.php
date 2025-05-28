<?php

namespace AnourValar\EloquentSerialize\Tests;

use AnourValar\EloquentSerialize\Tests\Models\User;
use AnourValar\EloquentSerialize\Tests\Models\UserPhone;
use AnourValar\EloquentSerialize\Tests\Models\UserPhonePrimary;

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

        // Etc
        $this->compare(User::withTrashed()->onlyTrashed());
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
        \Date::setTestNow('2025-05-28 19:00:00');

        // Model with SoftDelete
        $this->compare(
            User::withGlobalScope('userPhones', fn ($builder) => $builder->where('id', '<', 30)->has('userPhones'))
        );

        $this->compare(
            User::withTrashed()->withGlobalScope('userPhones', fn ($builder) => $builder->where('id', '<', 30)->has('userPhones'))
        );

        $this->compare(
            User::onlyTrashed()->withGlobalScope('userPhones', fn ($builder) => $builder->where('id', '<', 30)->has('userPhones'))
        );


        // Model without SoftDelete
        $this->compare(
            UserPhone::withGlobalScope('user', fn ($builder) => $builder->where('id', '<', 30)->has('user'))
        );

        $this->compare(
            UserPhone::withGlobalScope('user', fn ($builder) => $builder->has('user123'))->withoutGlobalScope('user')
        );


        // Model with alt global scope
        $this->compare(
            UserPhonePrimary::query()
        );

        $this->compare(
            UserPhonePrimary::withGlobalScope('foo', fn ($builder) => $builder->where('id', '<', 30))
        );

        $this->compare(
            UserPhonePrimary::withoutGlobalScope(\AnourValar\EloquentSerialize\Tests\Scopes\PrimaryScope::class)
        );

        $this->compare(
            UserPhonePrimary::query()
                ->withGlobalScope('foo', fn ($builder) => $builder->where('id', '<', 30))
                ->withoutGlobalScope(\AnourValar\EloquentSerialize\Tests\Scopes\PrimaryScope::class)
        );
    }

    /**
     * @return void
     */
    public function testPreApply()
    {
        $this->compare(
            User::query()
                ->withGlobalScope('userPhones', fn ($builder) => $builder->where('id', '<', 30)->has('userPhones'))
                ->applyScopes()
        );

        $this->compare(
            User::query()
                ->withTrashed()
                ->withGlobalScope('userPhones', fn ($builder) => $builder->where('id', '<', 30)->has('userPhones'))
                ->applyScopes()
        );

        $this->compare(
            User::query()
                ->onlyTrashed()
                ->withGlobalScope('userPhones', fn ($builder) => $builder->where('id', '<', 30)->has('userPhones'))
                ->applyScopes()
        );
    }
}
