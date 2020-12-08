<?php

namespace Neo4j\transport;

use Neo4j\auth\IAuth;

interface ITransport
{
    public function __construct(string $uri, IAuth $auth);

    public function request(string $api, string $data = '', string $method = 'POST'): string;

    public function setCustomHeaders(array $headers);
}
