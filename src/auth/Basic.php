<?php

namespace Neo4j\auth;

class Basic implements IAuth
{
    private $token;

    public function __construct(string $principal, string $credentials, string $realm = '', string $scheme = '', array $parameters = [])
    {
        $this->token = 'Basic ' . base64_encode($principal . ':' . $credentials);
    }

    public function __toString(): string
    {
        return $this->token;
    }
}
