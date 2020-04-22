<?php

namespace AnourValar\EloquentSerialize\Tests;

use AnourValar\EloquentSerialize\Tests\Models\User;
use AnourValar\EloquentSerialize\Tests\Models\UserPhone;

class EagerTest extends AbstractTest
{
    /**
     * @return void
     */
    public function testSimple()
    {
        // with
        $this->compare(User::with('userPhones'));

        // with count
        $this->compare(User::withCount('userPhones'));
    }

    /**
     * @return void
     */
    public function testWithBuilder()
    {
        // 1 level
        $this->compare(
            User::with(['userPhones' => function ($query)
            {
                $query->orderBy('id', 'ASC')->limit(1)->select(['id', 'phone']);
            }])
        );

        // 2 levels
        $this->compare(
            User::with(['userPhones' => function ($query)
            {
                $query->where(function ($query)
                {
                    $query->where('phone', '=', '111')->orWhere('phone', '=', '222');
                });
            }])
        );

        // 3 levels
        $this->compare(
            User::with(['userPhones' => function ($query)
            {
                $query->where(function ($query)
                {
                    $query
                        ->where('phone', '=', '111')
                        ->orWhere(function ($query)
                        {
                            $query->where('phone', '=', '222')->orWhere('created_at', '>', '2020-01-01');
                        });
                });
            }])
        );
    }

    /**
     * @return void
     */
    public function testWithCountBuilder()
    {
        // 1 level
        $this->compare(
            User::withCount(['userPhones' => function ($query)
            {
                $query->limit(2);
            }])
        );

        // 2 levels
        $this->compare(
            User::withCount(['userPhones' => function ($query)
            {
                $query->where(function ($query)
                {
                    $query->where('phone', '=', '111')->orWhere('phone', '=', '222');
                });
            }])
        );
    }

    /**
     * @return void
     */
    public function testWithCountAlias()
    {
        // simple
        $this->compare(
            User::withCount('userPhones as test')
        );

        // builder
        $this->compare(
            User::withCount([
                'userPhones as primary' => function ($query)
                {
                    $query->where(function ($query)
                    {
                        $query->where('is_primary', true);
                    });
                },
                'userPhones as not_primary' => function ($query)
                {
                    $query->where('is_primary', false);
                }
            ])
        );
    }

    /**
     * @return void
     */
    public function testBelongs()
    {
        // simple
        $this->compare(
            UserPhone::with('user')
        );

        // simple count
        $this->compare(
            UserPhone::withCount('user')
        );

        // builder
        $this->compare(
            UserPhone::with([
                'user' => function ($query)
                {
                    $query->where('title', '=', 'admin')->limit(1);
                }
            ])
        );
    }

    /**
     * @return void
     */
    public function testHasManyThrough()
    {
        // simple
        $this->compare(
            User::with('userPhoneNote')
        );

        // simple count
        $this->compare(
            User::withCount('userPhoneNote')
        );

        // builder
        $this->compare(
            User::with([
                'userPhoneNote' => function ($query)
                {
                    $query->limit(1);
                }
            ])
        );
    }

    /**
     * @return void
     */
    public function testHasOne()
    {
        // simple
        $this->compare(
            UserPhone::with('userPhoneNote')
        );

        // simple count
        $this->compare(
            UserPhone::withCount('userPhoneNote')
        );

        // builder
        $this->compare(
            UserPhone::with([
                'userPhoneNote' => function ($query)
                {
                    $query->whereNotNull('note');
                }
            ])
        );
    }
}
