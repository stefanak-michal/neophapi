<?php

namespace neophapi\encode;

/**
 * Interface IEncoder
 *
 * @author Michal Stefanak
 * @link https://github.com/stefanak-michal/neophapi
 * @package neophapi\encode
 */
interface IEncoder
{
    /**
     * @param array $statements
     * @return string
     */
    public function encode(array $statements): string;
}
