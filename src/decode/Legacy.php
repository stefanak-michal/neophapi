<?php


namespace Neo4j\decode;


class Legacy implements IDecoder
{

    public function decode(string $data): array
    {
        $decoded = json_decode($data, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg());
        }

        return $decoded;
    }
}
