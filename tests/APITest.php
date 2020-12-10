<?php

namespace neophapi\tests;

use neophapi\API;
use neophapi\Statement;
use Exception;

/**
 * Class API
 * @package neophapi\tests
 * @covers \neophapi\API
 * @covers \neophapi\Statement
 * @covers \neophapi\transport\Curl
 * @covers \neophapi\auth\Basic
 */
class APITest extends \PHPUnit\Framework\TestCase
{

    /**
     * @return API
     * @throws Exception
     */
    public function testInstance(): API
    {
        $auth = new \neophapi\auth\Basic($GLOBALS['NEO_USER'], $GLOBALS['NEO_PASS']);
        $this->assertInstanceOf(\neophapi\auth\Basic::class, $auth);

        $conn = new \neophapi\transport\Curl('http://localhost:7474', $auth);
        $this->assertInstanceOf(\neophapi\transport\Curl::class, $conn);

        try {
            $api = new API($conn);
            $this->assertInstanceOf(API::class, $api);
        } catch (Exception $e) {
            $this->markTestIncomplete($e->getMessage());
            exit;
        }

        return $api;
    }

    /**
     * @depends testInstance
     * @param API $api
     */
    public function testQuery(API $api)
    {
        $st = new Statement('RETURN 1 as num, 2 as cnt');
        $this->assertInstanceOf(Statement::class, $st);

        try {
            $result = $api->query($st);
            $this->assertIsArray($result);

            $this->assertEquals([
                ['num' => 1, 'cnt' => 2]
            ], $result);
        } catch (Exception $e) {
            $this->markTestIncomplete($e->getMessage());
            return;
        }
    }

    /**
     * @depends testInstance
     * @param API $api
     */
    public function testTransactionCommit(API $api)
    {
        try {
            $txid = $api->transaction();
            $result = $api->query(new Statement('CREATE (a:Test) RETURN ID(a) as id'), $txid);
            $this->assertIsArray($result);
            $this->assertNotEmpty($result);
            $api->query(new Statement('MATCH (a:Test) WHERE ID(a) = $a DELETE a', [
                'a' => $result[0]['id']
            ]), $txid);
            $this->assertTrue($api->commit($txid));
        } catch (Exception $e) {
            $this->markTestIncomplete($e->getMessage());
            return;
        }
    }

    /**
     * @depends testInstance
     * @param API $api
     */
    public function testTransactionRollback(API $api)
    {
        try {
            $txid = $api->transaction();
            $result = $api->query(new Statement('CREATE (a:Test) RETURN ID(a) as id'), $txid);
            $this->assertIsArray($result);
            $this->assertNotEmpty($result);
            $this->assertTrue($api->rollback($txid));
        } catch (Exception $e) {
            $this->markTestIncomplete($e->getMessage());
            return;
        }
    }

}
