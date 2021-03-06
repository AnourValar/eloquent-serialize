<?php

namespace AnourValar\EloquentSerialize\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \AnourValar\EloquentSerialize\Service serialize(\Illuminate\Database\Eloquent\Builder $builder)
 * @method static \AnourValar\EloquentSerialize\Service unserialize($package)
 */
class EloquentSerializeFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \AnourValar\EloquentSerialize\Service::class;
    }
}
