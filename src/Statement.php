<?php

namespace Neo4j;

final class Statement
{
    /**
     * @var string
     */
    private $query;

    /**
     * @var array
     */
    private $parameters;

    /**
     * Statement constructor.
     * @param string $query
     * @param array $parameters
     */
    public function __construct(string $query, array $parameters = [])
    {
        $this
            ->setQuery($query)
            ->setParameters($parameters);
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     * @return Statement
     */
    public function setQuery(string $query): Statement
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     * @return Statement
     */
    public function setParameters(array $parameters): Statement
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setParameter(string $key, $value): Statement
    {
        $this->parameters[$key] = $value;
        return $this;
    }

}
