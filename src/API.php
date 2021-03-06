<?php

namespace neophapi;

use neophapi\decode\IDecoder;
use neophapi\encode\IEncoder;
use neophapi\transport\ITransport;
use neophapi\decode\{Legacy as D_Legacy, V4_0 AS D_Default, Jolt as D_Jolt};
use neophapi\encode\{Legacy as E_Legacy, V4_0 AS E_Default};
use Exception;

/**
 * Class API
 *
 * @author Michal Stefanak
 * @link https://github.com/stefanak-michal/neophapi
 * @package neophapi
 */
final class API
{
    private const ENCODE_LEGACY = 10;
    private const ENCODE_DEFAULT = 11;

    private const DECODE_LEGACY = 20;
    private const DECODE_DEFAULT = 21;
    private const DECODE_JOLT = 22;

    /**
     * @var ITransport
     */
    private $transport;

    /**
     * @var string
     */
    private $database;

    /**
     * @var IEncoder
     */
    private $encoder;

    /**
     * @var IDecoder
     */
    private $decoder;

    /**
     * @var string
     */
    private $version;

    /**
     * API constructor.
     * @param ITransport $transport
     * @throws Exception
     */
    public function __construct(ITransport $transport)
    {
        $this->transport = $transport;
        $this->requestVersion();

        if (empty($this->version)) {
            throw new Exception('Could not resolve Neo4j version');
        }

        $this->setDatabase();

        if (version_compare($this->version, '4') == -1) {
            $this
                ->setEncoder(self::ENCODE_LEGACY)
                ->setDecoder(self::DECODE_LEGACY);
        } elseif (version_compare($this->version, '4.2') == -1) {
            $this
                ->setEncoder()
                ->setDecoder();
        } else {
            $this
                ->setEncoder()
                ->setDecoder(self::DECODE_JOLT);
        }
    }

    /**
     * @throws Exception
     */
    private function requestVersion()
    {
        $response = $this->transport->request('', '', 'GET');
        $this->version = json_decode($response, true)['neo4j_version'] ?? '';

        //version < 4
        if (empty($this->version)) {
            $response = $this->transport->request('db/data', '', 'GET');
            $this->version = json_decode($response, true)['neo4j_version'] ?? '';
        }

        if (empty($this->version)) {
            throw new Exception('Could not resolve Neo4j version');
        }
    }

    /**
     * @param string $database
     * @return API
     */
    public function setDatabase(string $database = 'neo4j'): API
    {
        $this->database = $database;
        return $this;
    }

    /**
     * @param int $encoder
     * @return API
     * @throws Exception
     */
    private function setEncoder(int $encoder = self::ENCODE_DEFAULT): API
    {
        switch ($encoder) {
            case self::ENCODE_LEGACY:
                $this->encoder = new E_Legacy();
                break;
            case self::ENCODE_DEFAULT:
                $this->encoder = new E_Default();
                break;
            default:
                throw new Exception('Invalid encoder');
        }
        return $this;
    }

    /**
     * @param int $decoder
     * @return API
     * @throws Exception
     */
    private function setDecoder(int $decoder = self::DECODE_DEFAULT): API
    {
        switch ($decoder) {
            case self::DECODE_LEGACY:
                $this->decoder = new D_Legacy();
                $this->decoder->setTransport($this->transport);
                break;
            case self::DECODE_DEFAULT:
                $this->decoder = new D_Default();
                break;
            case self::DECODE_JOLT:
                $this->transport->setCustomHeaders([
                    'Accept' => 'application/vnd.neo4j.jolt+json-seq' //;strict=true
                ]);
                $this->decoder = new D_Jolt();
                break;
            default:
                throw new Exception('Invalid decoder');
        }
        return $this;
    }

    /**
     * @param Statement $statement
     * @param int $txid Transaction ID
     * @return array
     * @throws Exception
     */
    public function query(Statement $statement, int $txid = -1): array
    {
        return $this->bulk([$statement], $txid)[0] ?? [];
    }

    /**
     * @return int
     * @throws Exception
     */
    public function transaction(): int
    {
        if (intval($this->version[0]) < 4) {
            throw new Exception('Old version does not support transaction');
        }

        $response = $this->transport->request('db/' . $this->database . '/tx');
        return $this->decoder->decodeTransactionId($response);
    }

    /**
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function commit(int $id): bool
    {
        if (intval($this->version[0]) < 4) {
            throw new Exception('Old version does not support transaction');
        }

        $response = $this->transport->request('db/' . $this->database . '/tx/' . $id . '/commit');
        $this->decoder->decode($response);

        return true;
    }

    /**
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function rollback(int $id): bool
    {
        if (intval($this->version[0]) < 4) {
            throw new Exception('Old version does not support transaction');
        }

        $response = $this->transport->request('db/' . $this->database . '/tx/' . $id, '', 'DELETE');
        $this->decoder->decode($response);

        return true;
    }

    /**
     * @param array $statements
     * @param int $txid Transaction ID
     * @return array \neophapi\Statement
     * @throws Exception
     */
    public function bulk(array $statements, int $txid = -1): array
    {
        $data = $this->encoder->encode($statements);

        switch (intval($this->version[0])) {
            case 3:
                $api = 'db/data/batch';
                break;
            case 4:
                $api = 'db/' . $this->database . '/tx/' . ($txid == -1 ? 'commit' : $txid);
                break;
            default:
                throw new Exception('Unsupported major version of Neo4j');
        }

        $response = $this->transport->request($api, $data);
        return $this->decoder->decode($response);
    }

}
