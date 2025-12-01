<?php

namespace AnourValar\EloquentSerialize\Grammars;

use Laravel\SerializableClosure\SerializableClosure;

trait EloquentBuilderGrammar
{
    /**
     * Serialize state for \Illuminate\Database\Eloquent\Builder
     */
    protected function packEloquentBuilder(\Illuminate\Database\Eloquent\Builder $builder): array
    {
        return [
            'with' => $this->getEagers($builder),
            'removed_scopes' => $builder->removedScopes(),
            'casts' => $builder->getModel()->getCasts(),
        ];
    }

    /**
     * Unserialize state for \Illuminate\Database\Eloquent\Builder
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

    private function getEagers(\Illuminate\Database\Eloquent\Builder $builder): array
    {
        $result = [];

        foreach ($builder->getEagerLoads() as $name => $value) {
            // Simply serialize the closure directly - no execution, no detection
            $result[$name] = [
                'closure' => serialize(new SerializableClosure($value)),
            ];
        }

        return $result;
    }

    private function setEagers(\Illuminate\Database\Eloquent\Builder $builder, array $eagers): void
    {
        foreach ($eagers as $name => &$value) {
            // Unserialize the closure and restore it
            $serializedClosure = unserialize($value['closure']);
            $value = $serializedClosure->getClosure();
        }
        unset($value);

        $builder->setEagerLoads($eagers);
    }

    private function cleanStaticConstraints(array &$packedQueryBuilder, array $packedReferenceQueryBuilder): void
    {
        $properties = [
            'aggregate', 'columns', 'distinct', 'wheres', 'groups', 'havings', 'orders', 'limit', 'offset', 'unions',
            'unionLimit', 'unionOffset', 'unionOrders', 'joins', 'groupLimit',
        ];

        foreach ($properties as $property) {
            if (! is_array($packedQueryBuilder[$property] ?? null)) {
                continue;
            }

            foreach ($packedQueryBuilder[$property] as $key => $item) {
                if (in_array($item, (array) ($packedReferenceQueryBuilder[$property] ?? null), true)) {
                    unset($packedQueryBuilder[$property][$key]);
                }
            }
        }

        foreach ($packedQueryBuilder['bindings'] as $binding => $data) {
            if (! is_array($data)) {
                continue; // just in case ;)
            }

            foreach ($data as $key => $value) {
                if (
                    isset($packedReferenceQueryBuilder['bindings'][$binding][$key])
                    && $packedReferenceQueryBuilder['bindings'][$binding][$key] === $value
                ) {
                    unset($packedQueryBuilder['bindings'][$binding][$key]);
                }
            }
        }
    }
}
