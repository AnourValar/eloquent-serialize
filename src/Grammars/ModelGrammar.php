<?php

namespace AnourValar\EloquentSerialize\Grammars;

use Illuminate\Database\Eloquent\Relations\HasOneOrMany;

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
        $serializeMorphableEager = fn ($value) => $this->serializeMorphableEager($value);
        $unserializeMorphableEager = fn ($value) => $this->unserializeMorphableEager($value);

        \Illuminate\Database\Eloquent\Relations\Relation::macro('importExtraParametersForSerialize', function (array $params) use ($unserializeMorphableEager) {
            foreach ($params as $key => $value) {
                if ($key == 'morphableEagerLoads' || $key == 'morphableEagerLoadCounts') {
                    $value = $unserializeMorphableEager($value);
                }

                $this->$key = $value;
            }
        });

        \Illuminate\Database\Eloquent\Relations\Relation::macro('exportExtraParametersForSerialize', function () use ($serializeMorphableEager) {
            if ($this instanceof \Illuminate\Database\Eloquent\Relations\MorphTo) {
                return [
                    'morphableEagerLoads' => $serializeMorphableEager($this->morphableEagerLoads),
                    'morphableEagerLoadCounts' => $serializeMorphableEager($this->morphableEagerLoadCounts),
                    'morphableConstraints' => $this->morphableConstraints,
                ];
            }

            if (
                $this instanceof HasOneOrMany
                && in_array(\Illuminate\Database\Eloquent\Relations\Concerns\SupportsInverseRelations::class, class_uses(HasOneOrMany::class)) // @TODO: >= 11.22
            ) {
                return [
                    'inverseRelationship' => $this->inverseRelationship,
                ];
            }

            return null;
        });
    }

    /**
     * @param array $value
     * @return array
     * @psalm-suppress UndefinedClass
     */
    private function serializeMorphableEager(array $value): array
    {
        foreach ($value as $class => &$items) {
            foreach ($items as $relation => &$item) {
                if (! is_callable($item)) {
                    continue;
                }

                if (! method_exists($class, $relation)) {
                    throw new \RuntimeException("Serialization error. Does relation '{$relation}' exists in the model '{$class}' ?");
                }

                // ALT: $obj->cleanStaticConstraints($item['builder'], $obj->packQueryBuilder((new $class)->getQuery()->getQuery()));
                $eloquentBuilder = (new $class())->$relation()->getModel()->newQuery();
                $item($eloquentBuilder);

                $item = [
                    'eloquent' => $this->packEloquentBuilder($eloquentBuilder),
                    'builder' => $this->packQueryBuilder($eloquentBuilder->getQuery()),
                ];
            }
            unset($item);
        }
        unset($items);

        return $value;
    }

    /**
     * @param array $value
     * @return array
     */
    private function unserializeMorphableEager(array $value): array
    {
        foreach ($value as &$items) {
            foreach ($items as &$item) {
                if (! is_array($item)) {
                    continue;
                }

                $item = function ($query) use ($item) {
                    if ($query instanceof \Illuminate\Database\Eloquent\Builder) {
                        $this->unpackEloquentBuilder($item['eloquent'], $query);
                        $this->unpackQueryBuilder($item['builder'], $query->getQuery());
                    } else {
                        $eloquent = $query->getQuery();
                        $this->unpackEloquentBuilder($item['eloquent'], $eloquent);
                        $this->unpackQueryBuilder($item['builder'], $query->getBaseQuery());
                    }
                };
            }
            unset($item);
        }
        unset($items);

        return $value;
    }
}
