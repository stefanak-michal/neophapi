<?php

namespace neophapi\transport;

use neophapi\auth\IAuth;

/**
 * Interface ITransport
 *
 * @author Michal Stefanak
 * @link https://github.com/stefanak-michal/neophapi
 * @package neophapi\transport
 */
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
