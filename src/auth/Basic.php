<?php

namespace neophapi\auth;

/**
 * Class Basic
 *
 * @author Michal Stefanak
 * @link https://github.com/stefanak-michal/neophapi
 * @package neophapi\auth
 */
class Basic implements IAuth
{
    /**
     * @var string
     */
    private $token;

    /**
     * @inheritDoc
     */
    public function __construct(string $principal, string $credentials, string $realm = '', string $scheme = '', array $parameters = [])
    {
        $this->token = 'Basic ' . base64_encode($principal . ':' . $credentials);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->token;
    }
}
