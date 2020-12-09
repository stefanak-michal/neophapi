<?php

namespace neophapi\structure;

/**
 * Class Path
 *
 * @author Michal Stefanak
 * @link https://github.com/stefanak-michal/neophapi
 * @package neophapi\structure
 */
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
        foreach ($nodes as $node) {
            if ($node instanceof Node) {
                $this->nodes[$node->id()] = $node;
            }
        }

        foreach ($relationships as $relationship) {
            if ($relationship instanceof Relationship) {
                $this->relationships[$relationship->id()] = $relationship;
            }
        }
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
