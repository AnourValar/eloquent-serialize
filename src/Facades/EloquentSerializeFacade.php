<?php

namespace AnourValar\EloquentSerialize\Facades;

use Illuminate\Support\Facades\Facade;

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
