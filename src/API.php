<?php

namespace neophapi;

use Exception;
use neophapi\decode\IDecoder;
use neophapi\encode\IEncoder;
use neophapi\transport\ITransport;

final class API
{

    const ENCODE_LEGACY = 10;
    const ENCODE_DEFAULT = 11;

    const DECODE_LEGACY = 20;
    const DECODE_DEFAULT = 21;
    const DECODE_JOLT = 22;

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
                $this->encoder = new \neophapi\encode\Legacy();
                break;
            case self::ENCODE_DEFAULT:
                $this->encoder = new \neophapi\encode\V4_0();
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
                $this->decoder = new \neophapi\decode\Legacy();
                $this->decoder->setTransport($this->transport);
                break;
            case self::DECODE_DEFAULT:
                $this->decoder = new \neophapi\decode\V4_0();
                break;
            case self::DECODE_JOLT:
                $this->decoder = new \neophapi\decode\Jolt();
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
        $decoded = $this->decoder->decode($response);

        if ($decoded['errors'] ?? false) {
            throw new Exception(implode(PHP_EOL, $decoded['errors']));
        }

        if (preg_match('/tx\/(\d+)\/commit/', $decoded['commit'], $matches) != 1) {
            throw new Exception('Parsing transaction ID unsuccessful');
        }

        return $matches[1];
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
        $decoded = $this->decoder->decode($response);

        if ($decoded['errors'] ?? false) {
            throw new Exception(implode(PHP_EOL, $decoded['errors']));
        }

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
        $decoded = $this->decoder->decode($response);

        if ($decoded['errors'] ?? false) {
            throw new Exception(implode(PHP_EOL, $decoded['errors']));
        }

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
        $decoded = $this->decoder->decode($response);

        if (!empty($decoded['errors'])) {
            $errors = array_map(function ($err) {
                if (is_array($err))
                    return $err['code'] . PHP_EOL . $err['message'];
                return $err;
            }, $decoded['errors']);
            throw new Exception(implode(PHP_EOL . PHP_EOL, $errors));
        }

        return $decoded;
    }

}
