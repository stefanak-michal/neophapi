<?php

namespace Neo4j\structure;

class Point
{
    /**
     * @var float
     */
    private $x;

    /**
     * @var float
     */
    private $y;

    /**
     * @var float
     */
    private $z;

    /**
     * @var string
     */
    private $crs;

    /**
     * @var int
     */
    private $srid;

    /**
     * Geospatial constructor.
     * @param float $x
     * @param float $y
     * @param float $z
     * @param string $crs
     * @param int $srid
     */
    public function __construct(float $x, float $y, float $z, string $crs = '', int $srid = 0)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->crs = $crs;
        $this->srid = $srid;
    }

    /**
     * @return float
     */
    public function x(): float
    {
        return $this->x;
    }

    /**
     * @return float decimal degrees
     */
    public function longitude(): float
    {
        return $this->x();
    }

    /**
     * @return float
     */
    public function y(): float
    {
        return $this->y;
    }

    /**
     * @return float decimal degrees
     */
    public function latitude(): float
    {
        return $this->y();
    }

    /**
     * @return float
     */
    public function z(): float
    {
        return $this->z;
    }

    /**
     * @return float meters
     */
    public function height(): float
    {
        return $this->z();
    }

    /**
     * @return string
     */
    public function crs(): string
    {
        return $this->crs;
    }

    /**
     * @return int
     */
    public function srid(): int
    {
        return $this->srid;
    }

}