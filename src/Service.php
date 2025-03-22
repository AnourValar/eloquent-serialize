<?php

namespace AnourValar\EloquentSerialize;

use Illuminate\Database\Eloquent\Relations\Relation;

class Service
{
    use \AnourValar\EloquentSerialize\Grammars\ModelGrammar;
    use \AnourValar\EloquentSerialize\Grammars\EloquentBuilderGrammar;
    use \AnourValar\EloquentSerialize\Grammars\QueryBuilderGrammar;

    /**
     * Pack
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $builder
     * @return string
     * @throws \RuntimeException
     */
    public function serialize(\Illuminate\Database\Eloquent\Builder|Relation $builder): string
    {
        if (
            $builder instanceof \Illuminate\Database\Eloquent\Relations\HasOne
            || $builder instanceof \Illuminate\Database\Eloquent\Relations\HasMany
            || $builder instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo // as well as MorphTo
            || $builder instanceof \Illuminate\Database\Eloquent\Relations\MorphOne
        ) {
            $builder = $builder->getQuery(); // chaperone/inverse is not supported!
        }

        if ($builder instanceof Relation) {
            throw new \RuntimeException(get_class($builder) . ' cannot be packed.');
        }

        $package = $this->pack($builder);

        return serialize($package); // important!
    }

    /**
     * Unpack
     *
     * @param mixed $package
     * @throws \LogicException
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function unserialize($package): \Illuminate\Database\Eloquent\Builder
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
