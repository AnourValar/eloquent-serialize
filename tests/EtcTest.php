<?php

namespace AnourValar\EloquentSerialize\Tests;

use AnourValar\EloquentSerialize\Tests\Models\User;

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
            (new User())->setConnection('mysql')->sharedLock()->where('id', '>', 0),
            false
        );
    }
}
