<?php

namespace neophapi\decode;

use Exception;

/**
 * Class ADecoder
 *
 * @author Michal Stefanak
 * @link https://github.com/stefanak-michal/neophapi
 * @package neophapi\decode
 */
abstract class ADecoder implements IDecoder
{
    /**
     * @param array $decoded
     * @throws Exception
     */
    protected function checkErrors(array $decoded)
    {
        $errors = [];
        foreach ($decoded['errors'] ?? [] as $error) {
            $errors[] = $error['code'] . PHP_EOL . $error['message'];
        }

        if (!empty($errors)) {
            throw new Exception(implode(PHP_EOL . PHP_EOL, $errors));
        }
    }

}
