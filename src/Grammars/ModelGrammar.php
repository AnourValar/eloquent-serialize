<?php

namespace AnourValar\EloquentSerialize\Grammars;

trait ModelGrammar
{
    /**
     * Pack
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \AnourValar\EloquentSerialize\Package
     */
    protected function pack(\Illuminate\Database\Eloquent\Builder $builder): \AnourValar\EloquentSerialize\Package
    {
        $this->setup();

        return new \AnourValar\EloquentSerialize\Package([
            'model' => get_class($builder->getModel()),
            'connection' => $builder->getModel()->getConnectionName(),
            'eloquent' => $this->packEloquentBuilder($builder),
            'query' => $this->packQueryBuilder($builder->getQuery()),
        ]);
    }

    /**
     * Unpack
     *
     * @param \AnourValar\EloquentSerialize\Package $package
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function unpack(\AnourValar\EloquentSerialize\Package $package): \Illuminate\Database\Eloquent\Builder
    {
        $this->setup();

        $builder = $package->get('model');
        $builder = $builder::on($package->get('connection'));

        $this->unpackEloquentBuilder($package->get('eloquent'), $builder);
        $this->unpackQueryBuilder($package->get('query'), $builder->getQuery());

        return $builder;
    }

    /**
     * init
     *
     * @return void
     */
    private function setup(): void
    {
        \Illuminate\Database\Eloquent\Relations\Relation::macro('importExtraParametersForSerialize', function (array $params) {
            foreach ($params as $key => $value) {
                $this->$key = $value;
            }
        });

        \Illuminate\Database\Eloquent\Relations\Relation::macro('exportExtraParametersForSerialize', function () {
            if ($this instanceof \Illuminate\Database\Eloquent\Relations\MorphTo) {
                return [
                    'morphableEagerLoads' => $this->morphableEagerLoads,
                    'morphableEagerLoadCounts' => $this->morphableEagerLoadCounts,
                    'morphableConstraints' => $this->morphableConstraints,
                ];
            }

            return null;
        });
    }
}
