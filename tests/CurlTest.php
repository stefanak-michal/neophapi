<?php

namespace neophapi\tests;

/**
 * Class CurlTest
 * @package neophapi\tests
 * @covers \neophapi\transport\Curl
 */
class CurlTest extends \PHPUnit\Framework\TestCase
{
    public function testCurlDestruct()
    {
        $auth = new \neophapi\auth\Basic($GLOBALS['NEO_USER'], $GLOBALS['NEO_PASS']);
        $this->assertInstanceOf(\neophapi\auth\Basic::class, $auth);

        $conn = new \neophapi\transport\Curl('http://localhost:7474', $auth);
        $this->assertInstanceOf(\neophapi\transport\Curl::class, $conn);

        unset($conn);
    }
}