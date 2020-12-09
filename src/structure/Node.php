<?php

namespace Neo4j\structure;

class Node
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var array
     */
    private $labels;

    /**
     * @var array
     */
    private $properties;

    public function __construct(int $id, array $labels, array $properties)
    {
        $this->id = $id;
        $this->labels = $labels;
        $this->properties = $properties;
    }

    /**
     * @return int
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function labels(): array
    {
        return $this->labels;
    }

    /**
     * @return array
     */
    public function properties(): array
    {
        return $this->properties;
    }
}
