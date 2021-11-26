<?php

namespace AnourValar\EloquentSerialize\Grammars;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait ModelGrammar
{
    /**
     * Pack
     *
     * @param Builder $builder
     * @return \AnourValar\EloquentSerialize\Package
     */
    protected function pack($builder): \AnourValar\EloquentSerialize\Package
    {
        return new \AnourValar\EloquentSerialize\Package([
            'query' => $this->packQueryBuilder($builder),
        ]);
    }

    /**
     * Unpack
     *
     * @param \AnourValar\EloquentSerialize\Package $package
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function unpack(\AnourValar\EloquentSerialize\Package $package)
    {
        $builder = DB::connection($package->get('connection'))->table('notOfInterest');

        $this->unpackQueryBuilder($package->get('query'), $builder);

        return $builder;
    }
}
