<?php

namespace AnourValar\EloquentSerialize;

use Nette\NotImplementedException;

class Service
{
    use \AnourValar\EloquentSerialize\Grammars\ModelGrammar;
    use \AnourValar\EloquentSerialize\Grammars\EloquentBuilderGrammar;
    use \AnourValar\EloquentSerialize\Grammars\QueryBuilderGrammar;

    /**
     * Pack
     * @param \Illuminate\Database\Query\Builder | \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return string
     */
    public function serialize($builder): string
    {
        switch (true) {
            case $builder instanceof \Illuminate\Database\Query\Builder:
                $package = $this->packQuery($builder);
                break;
            case $builder instanceof \Illuminate\Database\Eloquent\Builder:
                $package = $this->packEloquent($builder);
                break;
            default:
                throw new NotImplementedException('No implementation found for build (' . get_class($builder) . ')');
        }

        return serialize($package); // important!
    }

    /**
     * Unpack
     *
     * @param mixed $package
     * @throws \LogicException
     * @return \Illuminate\Database\Query\Builder | \Illuminate\Database\Eloquent\Builder
     */
    public function unserialize($package)
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
