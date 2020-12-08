<?php

namespace Neo4j\encode;

interface IEncoder
{
    public function encode(array $statements): string;
}
