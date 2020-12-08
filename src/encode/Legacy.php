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
            $collection[] = (object)[
                'method' => 'POST',
                'to' => '/cypher',
                'body' => [
                    'query' => $statement->getQuery(),
                    'params' => (object)$statement->getParameters()
                ]
            ];
        }

        return json_encode($collection);
    }
}
