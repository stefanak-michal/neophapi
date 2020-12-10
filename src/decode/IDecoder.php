<?php

namespace neophapi\decode;

use Exception;

/**
 * Interface IDecoder
 *
 * @author Michal Stefanak
 * @link https://github.com/stefanak-michal/neophapi
 * @package neophapi\decode
 */
interface IDecoder
{
    /**
     * @param string $message
     * @return array
     * @throws Exception
     */
    public function decode(string $message): array;

    /**
     * @param string $message
     * @return int
     * @throws Exception
     */
    public function decodeTransactionId(string $message): int;
}
