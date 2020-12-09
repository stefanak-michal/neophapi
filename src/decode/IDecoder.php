<?php

namespace Neo4j\decode;

use Exception;

interface IDecoder
{
    /**
     * @param string $data
     * @return array
     * @throws Exception
     */
    public function decode(string $data): array;
}
