<?php

namespace neophapi\decode;

use Exception;

interface IDecoder
{
    /**
     * @param string $message
     * @return array
     * @throws Exception
     */
    public function decode(string $message): array;
}
