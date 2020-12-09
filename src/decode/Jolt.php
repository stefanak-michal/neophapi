<?php

namespace neophapi\decode;

use neophapi\structure\{Node, Relationship, Path, Point};
use Exception;

/**
 * Class Jolt
 *
 * @author Michal Stefanak
 * @link https://github.com/stefanak-michal/neophapi
 * @package neophapi\decode
 */
class Jolt extends ADecoder
{
    /**
     * @inheritDoc
     */
    public function decode(string $message): array
    {
        var_dump($message);
        $output = $header = $data = [];

        $k = 0;
        foreach (explode("\n", $message) as $jsonString) {
            if (empty($jsonString))
                continue;

            $decoded = json_decode($jsonString, true);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new Exception(json_last_error_msg());
            }

            if (array_key_exists('error', $decoded)) {
                $this->checkErrors($decoded['error']);
            }

            if (array_key_exists('header', $decoded)) {
                $header = $decoded['header'];
                continue;
            }

            if (array_key_exists('data', $decoded)) {
                $output[$k][] = $this->processData($header['fields'], $decoded['data']);
            }

            if (array_key_exists('summary', $decoded)) {
                $k++;
            }
        }

        return $output;
    }

    /**
     * @param array $fields
     * @param array $data
     * @return array
     */
    private function processData(array $fields, array $data): array
    {
        $output = [];

        foreach ($data ?? [] as $i => $value) {

            if ($this->isTypeValue($value)) {
                $value = $this->getTypeValue($value);
            }

            $output[$fields[$i]] = $value;
        }

        return $output;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private function isTypeValue($value): bool
    {
        return is_array($value) && count($value) == 1 && in_array(key($value), ['?', 'Z', 'R', 'U', 'T', '@', '#', '[]', '{}', '()', '->', '<-', '..']);
    }

    /**
     * @param array $value
     * @return mixed
     */
    private function getTypeValue(array $value)
    {
        $v = reset($value);
        switch (key($value)) {
            case '?': // bool
                $value = boolval($v);
                break;
            case 'Z': // int
                $value = intval($v);
                break;
            case 'R': // float
                $value = floatval($v);
                break;
            case 'T': // time
                $value = strtotime($v);
                break;

            case '#': // base64
            case 'U': // string
                $value = $v;
                break;

            case '@': // point
                trigger_error('Jolt decode "Point" not implemented yet');
                break;

            case '[]': // list
            case '{}': // dictionary
                foreach ($v as $k => $val) {
                    if ($this->isTypeValue($val)) {
                        $val = $this->getTypeValue($val);
                    }
                    $v[$k] = $val;
                }
                $value = $v;
                break;

            case '()': // node
                $value = $this->node($v);
                break;

            case '->': // relationship
                $value = $this->relationship($v);
                break;

            case '<-': // relationship
                $value = $this->relationship($v, false);
                break;

            case '..': // path
                $value = $this->path($v);
                break;
        }

        return $value;
    }

    /**
     * @param array $value
     * @return Node
     */
    private function node(array $value): Node
    {
        list($id, $labels, $properties) = $value;

        foreach ($properties as $k => $val) {
            if ($this->isTypeValue($val)) {
                $val = $this->getTypeValue($val);
            }
            $properties[$k] = $val;
        }

        return new Node($id, $labels, $properties);
    }

    /**
     * @param array $value
     * @param bool $dir
     * @return Relationship
     */
    private function relationship(array $value, bool $dir = true): Relationship
    {
        if ($dir)
            list($id, $startNodeId, $type, $endNodeId, $properties) = $value;
        else
            list($id, $endNodeId, $type, $startNodeId, $properties) = $value;

        foreach ($properties as $k => $val) {
            if ($this->isTypeValue($val)) {
                $val = $this->getTypeValue($val);
            }
            $properties[$k] = $val;
        }

        return new Relationship($id, $startNodeId, $endNodeId, $type, $properties);
    }

    /**
     * @param array $value
     * @return Path
     */
    private function path(array $value): Path
    {
        $collection = [];
        foreach ($value as $element) {
            if ($this->isTypeValue($element)) {
                $collection[] = $this->getTypeValue($element);
            }
        }

        return new Path($collection, $collection);
    }
}
