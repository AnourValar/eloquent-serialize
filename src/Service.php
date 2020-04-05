<?php

namespace AnourValar\EloquentSerialize;

use AnourValar\EloquentSerialize\Grammars\EloquentBuilderGrammar;
use AnourValar\EloquentSerialize\Grammars\QueryBuilderGrammar;

class Service
{
    /**
     * @var \AnourValar\EloquentSerialize\Grammars\EloquentBuilderGrammar
     */
    private $eloquentBuilderGrammar;

    /**
     * @var \AnourValar\EloquentSerialize\Grammars\QueryBuilderGrammar
     */
    private $queryBuilderGrammar;

    /**
     * DI
     *
     * @param \AnourValar\EloquentSerialize\Grammars\EloquentBuilderGrammar $eloquentBuilderGrammar
     * @param \AnourValar\EloquentSerialize\Grammars\QueryBuilderGrammar $queryBuilderGrammar
     * @return void
     */
    public function __construct(EloquentBuilderGrammar $eloquentBuilderGrammar, QueryBuilderGrammar $queryBuilderGrammar)
    {
        $this->eloquentBuilderGrammar = $eloquentBuilderGrammar;
        $this->queryBuilderGrammar = $queryBuilderGrammar;
    }

    /**
     * Pack
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return string
     */
    public function serialize(\Illuminate\Database\Eloquent\Builder $builder) : string
    {
        $mapper = new Package([
            'model' => get_class($builder->getModel()), // model class
            'eloquent' => $this->eloquentBuilderGrammar->pack($builder), // state
            'query' => $this->queryBuilderGrammar->pack($builder->getQuery()), // state
        ]);

        return serialize($mapper); // important!
    }

    /**
     * Unpack
     *
     * @param mixed $data
     * @throws \Exception
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function unserialize($data) : \Illuminate\Database\Eloquent\Builder
    {
        // Canonize argument (data)
        if (is_string($data)) {
            $data = unserialize($data);
        }
        if (! ($data instanceof Package)) {
            throw new \Exception('Incorrect argument.');
        }

        // Create model
        $builder = $data->get('model');
        $builder = (new $builder)->newQuery();

        // Unpack state
        $this->eloquentBuilderGrammar->unpack($data->get('eloquent'), $builder);
        $this->queryBuilderGrammar->unpack($data->get('query'), $builder->getQuery());

        // Return builder
        return $builder;
    }
}
