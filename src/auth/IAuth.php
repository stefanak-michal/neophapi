<?php

namespace Neo4j\auth;

interface IAuth
{
    public function __construct(string $principal, string $credentials, string $realm = '', string $scheme = '', array $parameters = []);

    public function __toString(): string;
}
