<?php

namespace AnourValar\EloquentSerialize\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string serialize(\Illuminate\Database\Eloquent\Builder $builder)
 * @method static \Illuminate\Database\Eloquent\Builder unserialize(mixed $package)
 */
class EloquentSerializeFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \AnourValar\EloquentSerialize\Service::class;
    }
}
