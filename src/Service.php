<?php

namespace AnourValar\EloquentSerialize;

use Illuminate\Database\Query\Builder;

class Service
{
    use \AnourValar\EloquentSerialize\Grammars\ModelGrammar;
    use \AnourValar\EloquentSerialize\Grammars\EloquentBuilderGrammar;
    use \AnourValar\EloquentSerialize\Grammars\QueryBuilderGrammar;

    /**
     * Pack
     *
     * @return string
     */
    public function serialize(Builder $builder): string
    {
        $package = $this->pack($builder);

        return serialize($package); // important!
    }

    /**
     * Unpack
     *
     * @param mixed $package
     * @throws \LogicException
     * @return \Illuminate\Database\Query\Builder
     */
    public function unserialize($package): Builder
    {
        // Prepare data
        if (is_string($package)) {
            $package = unserialize($package);
        }
        if (! ($package instanceof Package)) {
            throw new \LogicException('Incorrect argument.');
        }

        // Unpack
        return $this->unpack($package);
    }
}
