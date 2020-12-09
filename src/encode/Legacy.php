<?php

namespace Neo4j\encode;

use Neo4j\Statement;

class Legacy implements IEncoder
{
    public function encode(array $statements): string
    {
        $collection = [];

        /** @var Statement $statement */
        foreach ($statements as $statement) {
            $query = $statement->getQuery();
            foreach ($statement->getParameters() as $key => $value) {
                $query = str_replace('$' . $key, '{' . $key . '}', $query);
            }

            $collection[] = (object)[
                'method' => 'POST',
                'to' => '/cypher',
                'body' => [
                    'query' => $query,
                    'params' => (object)$statement->getParameters()
                ]
            ];
        }

        return json_encode($collection);
    }
}
