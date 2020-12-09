<?php

namespace neophapi\transport;

use neophapi\auth\IAuth;

interface ITransport
{
    /**
     * ITransport constructor.
     * @param string $uri
     * @param IAuth $auth
     */
    public function __construct(string $uri, IAuth $auth);

    /**
     * @param string $api
     * @param string $data
     * @param string $method
     * @return string
     */
    public function request(string $api, string $data = '', string $method = 'POST'): string;

    /**
     * @param array $headers
     */
    public function setCustomHeaders(array $headers);
}
