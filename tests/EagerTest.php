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
}
