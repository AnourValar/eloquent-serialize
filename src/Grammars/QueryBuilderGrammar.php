<?php

namespace AnourValar\EloquentSerialize\Grammars;

class QueryBuilderGrammar
{
    /**
     * Serialize state for \Illuminate\Database\Query\Builder
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @return array
     */
    public function pack(\Illuminate\Database\Query\Builder $builder) : array
    {
        $result = [
            'bindings' => $builder->bindings,
            'aggregate' => $builder->aggregate,
            'columns' => $builder->columns,
            'distinct' => $builder->distinct,
            'from' => $builder->from,
            'joins' => $builder->joins,
            'wheres' => $builder->wheres,
            'groups' => $builder->groups,
            'havings' => $builder->havings,
            'orders' => $builder->orders,
            'limit' => $builder->limit,
            'offset' => $builder->offset,
            'unions' => $builder->unions,
            'unionLimit' => $builder->unionLimit,
            'unionOffset' => $builder->unionOffset,
            'unionOrders' => $builder->unionOrders,
        ];

        foreach ($result['wheres'] as &$item) {
            if (isset($item['query'])) {
                $item['query'] = $this->pack($item['query']);
            }
        }
        unset($item);

        return $result;
    }

    /**
     * @param array $data
     * @param \Illuminate\Database\Query\Builder $builder
     * @return \Illuminate\Database\Query\Builder
     */
    public function unpack(array $data, \Illuminate\Database\Query\Builder $builder) : \Illuminate\Database\Query\Builder
    {
        foreach ($data as $key => $value) {
            if ($key == 'wheres') {
                foreach ($value as &$item) {

                    if (isset($item['query'])) {
                        $item['query'] = $this->unpack($item['query'], $builder->newQuery());
                    }
                }
                unset($item);
            }

            if (is_array($builder->$key) && is_array($value)) {
                $builder->$key = array_merge_recursive($builder->$key, $value);
            } else {
                $builder->$key = $value;
            }
        }

        return $builder;
    }
}
