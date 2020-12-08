<?php

namespace Neo4j\decode;

interface IDecoder
{
    public function decode(string $data): array;
}
