<?php

namespace AnourValar\EloquentSerialize\Grammars;

trait EloquentBuilderGrammar
{
    /**
     * Serialize state for \Illuminate\Database\Eloquent\Builder
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return array
     */
    protected function packEloquentBuilder(\Illuminate\Database\Eloquent\Builder $builder): array
    {
        return [
            'with' => $this->getEagers($builder), // preloaded ("eager") relations
            'removed_scopes' => $builder->removedScopes(), // global scopes
            'casts' => $builder->getModel()->getCasts(),
        ];
    }

    /**
     * Unserialize state for \Illuminate\Database\Eloquent\Builder
     *
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function unpackEloquentBuilder(array $data, \Illuminate\Database\Eloquent\Builder &$builder): void
    {
        // Preloaded ("eager") relations
        $this->setEagers($builder, $data['with']);

        // Global scopes
        if ($data['removed_scopes']) {
            $builder->withoutGlobalScopes($data['removed_scopes']);
        }

        // Casts
        if (method_exists($builder->getModel(), 'mergeCasts')) { // old versions support
            $builder->getModel()->mergeCasts($data['casts']);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return array
     */
    private function getEagers(\Illuminate\Database\Eloquent\Builder $builder): array
    {
        $result = [];

        foreach ($builder->getEagerLoads() as $name => $value) {
            $relation = $builder;
            foreach (explode('.', $name) as $part) {
                $relation = $relation->getRelation($part); // get a relation without "constraints"
            }
            $value($relation); // apply closure

            $result[$name] = $this->packQueryBuilder($relation->getQuery()->getQuery());
        }

        return $result;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $eagers
     * @return void
     */
    private function setEagers(\Illuminate\Database\Eloquent\Builder $builder, array $eagers): void
    {
        foreach ($eagers as &$value) {
            $value = function ($query) use ($value)
            {
                // Input argument may be different depends on context
                while (! ($query instanceof \Illuminate\Database\Query\Builder)) {
                    $query = $query->getQuery();
                }

                $query->joins = null; // "no constraints" issue

                $this->unpackQueryBuilder($value, $query);
            };
        }
        unset($value);

        $builder->setEagerLoads($eagers);
    }
}
