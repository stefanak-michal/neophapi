<?php

namespace neophapi\structure;

class Path
{
    /**
     * @var array
     */
    private $nodes;

    /**
     * @var array
     */
    private $relationships;

    /**
     * Path constructor.
     * @param array $nodes
     * @param array $relationships
     */
    public function __construct(array $nodes, array $relationships)
    {
        $this->nodes = array_filter($nodes, function($item) {
            return $item instanceof Node;
        });

        $this->relationships = array_filter($relationships, function($item) {
            return $item instanceof Relationship;
        });
    }

    /**
     * @return array \neophapi\structure\Node
     */
    public function nodes(): array
    {
        return $this->nodes;
    }

    /**
     * @return array \neophapi\structure\Relationship
     */
    public function relationships(): array
    {
        return $this->relationships;
    }
}
