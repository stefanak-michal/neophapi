<?php


namespace neophapi\decode;

use Exception;


class V4_0 implements IDecoder
{

    /**
     * @inheritDoc
     */
    public function decode(string $data): array
    {
        $decoded = json_decode($data, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new Exception(json_last_error_msg());
        }

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
     * @param array $nodes
     * @param array $meta
     * @return \neophapi\structure\Node
     * @throws Exception
     */
    private function node(array $nodes, array $meta): \neophapi\structure\Node
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

        return new \neophapi\structure\Node($node['id'], $node['labels'], $node['properties']);
    }

    /**
     * @param array $relationships
     * @param array $meta
     * @return \neophapi\structure\Relationship
     * @throws Exception
     */
    private function relationship(array $relationships, array $meta): \neophapi\structure\Relationship
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

        return new \neophapi\structure\Relationship($rel['id'], $rel['startNode'], $rel['endNode'], $rel['type'], $rel['properties']);
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
     * @return \neophapi\structure\Path
     * @throws Exception
     */
    private function path(array $graph, array $meta): \neophapi\structure\Path
    {
        $nodes = $relationships = [];

        foreach ($meta as $item) {
            switch ($item['type']) {
                case 'node':
                    $node = $this->node($graph['nodes'], $item);
                    $nodes[$node->id()] = $node;
                    break;
                case 'relationship':
                    $relationship = $this->relationship($graph['relationships'], $item);
                    $relationships[$relationship->id()] = $relationship;
                    break;
            }
        }

        return new \neophapi\structure\Path($nodes, $relationships);
    }

    /**
     * @param array $row
     * @return \neophapi\structure\Point
     */
    private function point(array $row): \neophapi\structure\Point
    {
        return new \neophapi\structure\Point(
            $row['coordinates'][0] ?? 0,
            $row['coordinates'][1] ?? 0,
            $row['coordinates'][2] ?? 0,
            $row['crs']['name'] ?? '',
            $row['crs']['srid'] ?? 0
        );
    }
}
