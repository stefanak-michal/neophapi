<?php

namespace neophapi\auth;

/**
 * Interface IAuth
 *
 * @author Michal Stefanak
 * @link https://github.com/stefanak-michal/neophapi
 * @package neophapi\auth
 */
interface IAuth
{
    /**
     * IAuth constructor.
     * @param string $principal
     * @param string $credentials
     * @param string $realm
     * @param string $scheme
     * @param array $parameters
     */
    public function __construct(string $principal, string $credentials, string $realm = '', string $scheme = '', array $parameters = []);

    /**
     * @return string
     */
    public function __toString(): string;
}
