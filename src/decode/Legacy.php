<?php

namespace neophapi\decode;

use Exception;

class Legacy implements IDecoder
{
    /**
     * @var \neophapi\transport\ITransport
     */
    private $transport;

    /**
     * @inheritDoc
     */
    public function decode(string $data): array
    {
        $decoded = json_decode($data, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg());
        }

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
     * @param \neophapi\transport\ITransport $transport
     */
    public function setTransport(\neophapi\transport\ITransport $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param array $value
     * @return \neophapi\structure\Node
     */
    private function node(array $value): \neophapi\structure\Node
    {
        return new \neophapi\structure\Node($value['metadata']['id'], $value['metadata']['labels'], $value['data']);
    }

    /**
     * @param array $value
     * @return \neophapi\structure\Relationship
     * @throws Exception
     */
    private function relationship(array $value): \neophapi\structure\Relationship
    {
        return new \neophapi\structure\Relationship(
            $value['metadata']['id'],
            $this->parseId($value['start']),
            $this->parseId($value['end']),
            $value['metadata']['type'],
            $value['data']
        );
    }

    /**
     * @param array $value
     * @return \neophapi\structure\Path
     * @throws Exception
     */
    private function path(array $value): \neophapi\structure\Path
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
                $node = $this->node($result['body']);
                $nodes[$node->id()] = $node;
            } elseif (array_key_exists('type', $result['body']['metadata'])) {
                $relationship = $this->relationship($result['body']);
                $relationships[$relationship->id()] = $relationship;
            }
        }

        return new \neophapi\structure\Path($nodes, $relationships);
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
