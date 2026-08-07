<?php

namespace AnourValar\EloquentSerialize\Tests;

use AnourValar\EloquentSerialize\Tests\Models\Tag;
use AnourValar\EloquentSerialize\Tests\Models\User;
use AnourValar\EloquentSerialize\Tests\Models\UserPhone;

class EtcTest extends AbstractSuite
{
    /**
     * @return void
     */
    public function testLock()
    {
        $this->compare(
            (new User())->setConnection('mysql')->lockForUpdate(),
            false
        );

        $this->compare(
            (new User())->setConnection('mysql')->lock('lockForUpdate'),
            false
        );

        $this->compare(
            (new User())->setConnection('mysql')->lock('FOR UPDATE NOWAIT'),
            false
        );

        $this->compare(
            (new User())->setConnection('mysql')->sharedLock()->where('id', '>', 0),
            false
        );
    }

    /**
     * @return void
     */
    public function testLockUseWritePdo()
    {
        $builders = [
            'lockForUpdate' => (new User())->setConnection('mysql')->lockForUpdate(),
            'lock' => (new User())->setConnection('mysql')->lock('FOR UPDATE NOWAIT'),
            'sharedLock' => (new User())->setConnection('mysql')->sharedLock(),
            'useWritePdo' => (new User())->setConnection('mysql')->useWritePdo(),
        ];

        foreach ($builders as $name => $builder) {
            $this->assertTrue($builder->getQuery()->useWritePdo, $name); // sanity check

            $builder = $this->service->unserialize($this->service->serialize($builder));

            $this->assertTrue($builder->getQuery()->useWritePdo, $name);
        }
    }

    /**
     * @return void
     */
    public function testPackRelation()
    {
        $this->compare(UserPhone::first()->userPhoneNote()); // HasOne [not with chaperone!]
        $this->compare(User::first()->filesDE()); // HasOne + conditions

        $this->compare(User::first()->userPhones()); // HasMany
        $this->compare(User::first()->filesAB()); // HasMany + conditions

        $this->compare(UserPhone::first()->user()); // BelongsTo

        $this->compare(UserPhone::first()->tag()); // MorphOne
        $this->compare(Tag::first()->taggable()); // MorphTo
    }

    /**
     * @return void
     */
    public function testPackRelationNotSupported1()
    {
        $this->expectException(\RuntimeException::class);
        $this->compare(User::first()->userPhoneNoteAlt()); // HasOneThrough
    }

    /**
     * @return void
     */
    public function testPackRelationNotSupported2()
    {
        $this->expectException(\RuntimeException::class);
        $this->compare(User::first()->userPhoneNote()); // HasManyThrough
    }

    /**
     * @return void
     */
    public function testPackRelationNotSupported3()
    {
        $this->expectException(\RuntimeException::class);
        $this->compare(User::first()->books()); // BelongsToMany
    }
}
