<?php

namespace Neo4j\encode;

use Neo4j\Statement;

class V4_0 implements IEncoder
{
    public function encode(array $statements): string
    {
        $collection = [];

        /** @var Statement $statement */
        foreach ($statements as $statement) {
            $collection[] = [
                'statement' => $statement->getQuery(),
                'parameters' => (object)$statement->getParameters()
            ];
        }

        return json_encode([
            'statements' => $collection
        ]);
    }
}
