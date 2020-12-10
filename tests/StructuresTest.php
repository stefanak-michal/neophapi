<?php


namespace neophapi\tests;

use \neophapi\structure\{Node, Relationship, Path, Point};

/**
 * Class StructuresTest
 * @package neophapi\tests
 * @covers \neophapi\structure\Node
 * @covers \neophapi\structure\Relationship
 * @covers \neophapi\structure\Path
 * @covers \neophapi\structure\Point
 */
class StructuresTest extends \PHPUnit\Framework\TestCase
{
    public function testNode()
    {
        $id = 1;
        $labels = ['Test'];
        $properties = ['abc' => 123];

        $node = new Node($id, $labels, $properties);
        $this->assertInstanceOf(Node::class, $node);

        $this->assertEquals($id, $node->id());
        $this->assertEquals($labels, $node->labels());
        $this->assertEquals($properties, $node->properties());
    }

    public function testRelationship()
    {
        $id = 1;
        $startNodeId = 10;
        $endNodeId = 20;
        $type = 'HAS';
        $properties = ['def' => 456];

        $rel = new Relationship($id, $startNodeId, $endNodeId, $type, $properties);
        $this->assertInstanceOf(Relationship::class, $rel);

        $this->assertEquals($id, $rel->id());
        $this->assertEquals($startNodeId, $rel->startNodeId());
        $this->assertEquals($endNodeId, $rel->endNodeId());
        $this->assertEquals($type, $rel->type());
        $this->assertEquals($properties, $rel->properties());
    }

    public function testPath()
    {
        $node1 = new Node(1, ['Test'], []);
        $this->assertInstanceOf(Node::class, $node1);
        $node2 = new Node(1, ['Test'], []);
        $this->assertInstanceOf(Node::class, $node2);

        $rel = new Relationship(0, $node1->id(), $node2->id(), 'HAS', []);
        $this->assertInstanceOf(Relationship::class, $rel);

        $path = new Path([$node1, $node2], [$rel]);
        $this->assertInstanceOf(Path::class, $path);

        $this->assertEquals([
            $node1->id() => $node1,
            $node2->id() => $node2
        ], $path->nodes());

        $this->assertEquals([
            $rel->id() => $rel
        ], $path->relationships());
    }

    public function testPoint()
    {
        $point = new Point(4, 5, 6, 9157);
        $this->assertInstanceOf(Point::class, $point);

        $this->assertEquals(4, $point->x());
        $this->assertEquals(5, $point->y());
        $this->assertEquals(6, $point->z());

        $this->assertEquals($point->x(), $point->longitude());
        $this->assertEquals($point->y(), $point->latitude());
        $this->assertEquals($point->z(), $point->height());

        $this->assertEquals(9157, $point->srid());
    }
}