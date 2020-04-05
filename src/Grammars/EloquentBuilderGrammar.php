<?php

namespace AnourValar\EloquentSerialize\Grammars;

class EloquentBuilderGrammar
{
    /**
     * @var \AnourValar\EloquentSerialize\Grammars\QueryBuilderGrammar
     */
    private $queryBuilderGrammar;

    /**
     * DI
     *
     * @param \AnourValar\EloquentSerialize\Grammars\QueryBuilderGrammar $queryBuilderGrammar
     * @return void
     */
    public function __construct(\AnourValar\EloquentSerialize\Grammars\QueryBuilderGrammar $queryBuilderGrammar)
    {
        $this->queryBuilderGrammar = $queryBuilderGrammar;
    }

    /**
     * Serialize state for \Illuminate\Database\Eloquent\Builder
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return array
     */
    public function pack(\Illuminate\Database\Eloquent\Builder $builder) : array
    {
        return [
            'with' => $this->getEagers($builder), // preloaded ("eager") relations
            'removed_scopes' => $builder->removedScopes(), // global scopes
        ];
    }

    /**
     * Unserialize state for \Illuminate\Database\Eloquent\Builder
     *
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function unpack(array $data, \Illuminate\Database\Eloquent\Builder &$builder) : void
    {
        // Preloaded ("eager") relations
        $this->setEagers($builder, $data['with']);

        // Global scopes
        if ($data['removed_scopes']) {
            $builder->withoutGlobalScopes($data['removed_scopes']);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return array
     */
    private function getEagers(\Illuminate\Database\Eloquent\Builder $builder) : array
    {
        $result = [];

        foreach ($builder->getEagerLoads() as $name => $value) {
            $model = get_class($builder->getModel());
            $query =  \Illuminate\Database\Eloquent\Relations\HasMany::noConstraints(function() use ($model, $name)
            {
                $object = null;

                foreach (explode('.', $name) as $name) {
                    if (! isset($object)) {
                        $object = new $model;
                    } else {
                        $object = $object->getModel();
                    }

                    $object = $object->$name();
                }

                return $object->getQuery()->newQuery();
            });

            $value($query);
            $result[$name] = $this->queryBuilderGrammar->pack($query->getQuery());
        }

        return $result;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $eagers
     * @return void
     */
    private function setEagers(\Illuminate\Database\Eloquent\Builder $builder, array $eagers) : void
    {
        foreach ($eagers as &$value) {
            $value = function ($query) use ($value)
            {
                // Input argument may be different depends on context
                while (! ($query instanceof \Illuminate\Database\Query\Builder)) {
                    $query = $query->getQuery();
                }

                $this->queryBuilderGrammar->unpack($value, $query);
            };
        }
        unset($value);

        $builder->setEagerLoads($eagers);
    }
}
