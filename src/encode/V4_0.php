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
            $query = $statement->getQuery();
            foreach ($statement->getParameters() as $key => $value) {
                $query = str_replace('{' . $key . '}', '$' . $key, $query);
            }

            $collection[] = [
                'statement' => $query,
                'parameters' => (object)$statement->getParameters(),
                'resultDataContents' => [
                    'row',
                    'graph'
                ]
            ];
        }

        return json_encode([
            'statements' => $collection
        ]);
    }
}
