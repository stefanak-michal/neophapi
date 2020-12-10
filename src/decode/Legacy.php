<?php

namespace neophapi\decode;

use neophapi\structure\{Node, Relationship, Path};
use neophapi\transport\ITransport;
use Exception;

/**
 * Class Legacy
 *
 * @author Michal Stefanak
 * @link https://github.com/stefanak-michal/neophapi
 * @package neophapi\decode
 */
class Legacy extends ADecoder
{
    /**
     * @var ITransport
     */
    private $transport;

    /**
     * @inheritDoc
     */
    public function decode(string $message): array
    {
        $decoded = json_decode($message, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg());
        }

        $this->checkErrors($decoded);

        $output = [];
        foreach ($decoded ?? [] as $result) {
            $tmp1 = [];
            foreach ($result['body']['data'] ?? [] as $row) {
                $tmp2 = [];
                foreach ($row ?? [] as $i => $value) {

                    if (array_key_exists('metadata', $value)) {
                        if (array_key_exists('id', $value['metadata'])) {
                            if (array_key_exists('labels', $value['metadata'])) {
                                $value = $this->node($value);
                            } elseif (array_key_exists('type', $value['metadata'])) {
                                $value = $this->relationship($value);
                            }
                        }
                    } elseif (array_key_exists('nodes', $value) && array_key_exists('relationships', $value)) {
                        $value = $this->path($value);
                    }

                    $tmp2[$result['body']['columns'][$i]] = $value;

                }
                $tmp1[] = $tmp2;
            }
            $output[] = $tmp1;
        }


        return $output;
    }

    /**
     * @inheritDoc
     */
    public function decodeTransactionId(string $message): int
    {
        throw new Exception('Legacy version does not support transactions');
    }

    /**
     * @param ITransport $transport
     */
    public function setTransport(ITransport $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param array $value
     * @return Node
     */
    private function node(array $value): Node
    {
        return new Node($value['metadata']['id'], $value['metadata']['labels'], $value['data']);
    }

    /**
     * @param array $value
     * @return Relationship
     * @throws Exception
     */
    private function relationship(array $value): Relationship
    {
        return new Relationship(
            $value['metadata']['id'],
            $this->parseId($value['start']),
            $this->parseId($value['end']),
            $value['metadata']['type'],
            $value['data']
        );
    }

    /**
     * @param array $value
     * @return Path
     * @throws Exception
     */
    private function path(array $value): Path
    {
        $nodes = $relationships = [];

        $data = [];
        foreach ($value['nodes'] as $url) {
            $data[] = (object)[
                'method' => 'GET',
                'to' => '/node/' . $this->parseId($url)
            ];
        }

        foreach ($value['relationships'] as $url) {
            $data[] = (object)[
                'method' => 'GET',
                'to' => '/relationship/' . $this->parseId($url)
            ];
        }

        $response = $this->transport->request('db/data/batch', json_encode($data));
        $decoded = json_decode($response, true);

        foreach ($decoded as $result) {
            if (array_key_exists('labels', $result['body']['metadata'])) {
                $nodes[] = $this->node($result['body']);
            } elseif (array_key_exists('type', $result['body']['metadata'])) {
                $relationships[] = $this->relationship($result['body']);
            }
        }

        return new Path($nodes, $relationships);
    }

    /**
     * @param string $value
     * @return int
     * @throws Exception
     */
    private function parseId(string $value): int
    {
        if (preg_match('/\d+$/', $value, $match) == 1) {
            return intval($match[0]);
        }

        throw new Exception('Unsuccessful parsing ID from value: ' . $value);
    }

}
