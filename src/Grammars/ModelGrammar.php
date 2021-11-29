<?php

namespace AnourValar\EloquentSerialize\Grammars;

trait ModelGrammar
{
    /**
     * Pack
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @return \AnourValar\EloquentSerialize\Package
     */
    protected function packEloquent(\Illuminate\Database\Eloquent\Builder $builder): \AnourValar\EloquentSerialize\Package
    {
        return new \AnourValar\EloquentSerialize\Package([
            'model' => get_class($builder->getModel()),
            'connection' => $builder->getModel()->getConnectionName(),
            'eloquent' => $this->packEloquentBuilder($builder),
            'query' => $this->packQueryBuilder($builder->getQuery()),
        ]);
    }

    /**
     * Pack
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @return \AnourValar\EloquentSerialize\Package
     */
    protected function packQuery(\Illuminate\Database\Query\Builder $builder): \AnourValar\EloquentSerialize\Package
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
        $model = $package->get('model');

        $builder = isset($model)
            ? $model::on($package->get('connection'))
            : \Illuminate\Support\Facades\DB::connection($package->get('connection'))->table('notOfInterest');


        if ($builder instanceof \Illuminate\Database\Eloquent\Builder) {
            $this->unpackEloquentBuilder($package->get('eloquent'), $builder);
        }
        $this->unpackQueryBuilder($package->get('query'), $builder);

        return $builder;
    }
}
