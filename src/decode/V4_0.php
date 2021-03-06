<?php

namespace neophapi\decode;

use neophapi\structure\{Node, Relationship, Path, Point};
use Exception;

/**
 * Class V4_0 (Default)
 *
 * @author Michal Stefanak
 * @link https://github.com/stefanak-michal/neophapi
 * @package neophapi\decode
 */
class V4_0 extends ADecoder
{

    /**
     * @inheritDoc
     */
    public function decode(string $message): array
    {
        $decoded = json_decode($message, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new Exception(json_last_error_msg());
        }

        $this->checkErrors($decoded);

        $output = [];
        foreach ($decoded['results'] ?? [] as $result) {
            $tmp1 = [];
            foreach ($result['data'] ?? [] as $row) {
                $tmp2 = [];
                foreach ($row['row'] ?? [] as $i => $value) {

                    switch ($row['meta'][$i]['type'] ?? '') {
                        case 'node':
                            $value = $this->node($row['graph']['nodes'], $row['meta'][$i]);
                            break;
                        case 'relationship':
                            $value = $this->relationship($row['graph']['relationships'], $row['meta'][$i]);
                            break;
                        case 'point':
                            $value = $this->point($value);
                            break;
                    }

                    if ($this->isPath($row, $i)) {
                        $value = $this->path($row['graph'], $row['meta'][$i]);
                    }

                    $tmp2[$result['columns'][$i]] = $value;

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
        $decoded = json_decode($message, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new Exception(json_last_error_msg());
        }

        $this->checkErrors($decoded);

        if (array_key_exists('commit', $decoded) && preg_match('/tx\/(\d+)\/commit/', $decoded['commit'], $matches) == 1) {
            return intval($matches[1]);
        }

        throw new Exception('Unsuccessful decode of transaction ID from message: ' . PHP_EOL . $message);
    }

    /**
     * @param array $nodes
     * @param array $meta
     * @return Node
     * @throws Exception
     */
    private function node(array $nodes, array $meta): Node
    {
        $node = [];
        foreach ($nodes as $node) {
            if ($node['id'] == $meta['id']) {
                break;
            }
        }

        if (empty($node)) {
            throw new Exception('Node not found in result data');
        }

        return new Node($node['id'], $node['labels'], $node['properties']);
    }

    /**
     * @param array $relationships
     * @param array $meta
     * @return Relationship
     * @throws Exception
     */
    private function relationship(array $relationships, array $meta): Relationship
    {
        $rel = [];
        foreach ($relationships as $rel) {
            if ($rel['id'] == $meta['id']) {
                break;
            }
        }

        if (empty($rel)) {
            throw new Exception('Relationship not found in result data');
        }

        return new Relationship($rel['id'], $rel['startNode'], $rel['endNode'], $rel['type'], $rel['properties']);
    }

    /**
     * @param array $row
     * @param int $i
     * @return bool
     */
    private function isPath(array $row, int $i): bool
    {
        $filtered = array_filter($row['meta'][$i] ?? [], function ($item) {
            return is_array($item) && array_key_exists('type', $item) && in_array($item['type'], ['node', 'relationship']);
        });

        return count($filtered) > 0;
    }

    /**
     * @param array $graph
     * @param array $meta
     * @return Path
     * @throws Exception
     */
    private function path(array $graph, array $meta): Path
    {
        $nodes = $relationships = [];

        foreach ($meta as $item) {
            switch ($item['type']) {
                case 'node':
                    $nodes[] = $this->node($graph['nodes'], $item);
                    break;
                case 'relationship':
                    $relationships[] = $this->relationship($graph['relationships'], $item);
                    break;
            }
        }

        return new Path($nodes, $relationships);
    }

    /**
     * @param array $row
     * @return Point
     */
    private function point(array $row): Point
    {
        return new Point(
            $row['coordinates'][0] ?? 0,
            $row['coordinates'][1] ?? 0,
            $row['coordinates'][2] ?? 0,
            $row['crs']['srid'] ?? 0
        );
    }
}
