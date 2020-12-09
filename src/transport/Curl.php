<?php


namespace neophapi\transport;


use neophapi\auth\IAuth;
use Exception;

class Curl implements ITransport
{

    /**
     * @var resource
     */
    private $ch;

    /**
     * @var array
     */
    private $defaultHeaders;

    /**
     * @var array
     */
    private $customHeaders = [];

    /**
     * @var string
     */
    private $uri;

    /**
     * @inheritDoc
     */
    public function __construct(string $uri, IAuth $auth, int $timeout = 3)
    {
        $this->defaultHeaders = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json; charset=UTF-8',
            'Authorization' => (string)$auth
        ];

        //replace hostname for IP .. it will increase speed to avoid DNS resolve
        $host = parse_url($uri, PHP_URL_HOST);
        if (filter_var($host, FILTER_VALIDATE_IP) === false) {
            $uri = substr_replace($uri, gethostbyname($host), strpos($uri, $host), strlen($host));
        }
        $this->uri = rtrim($uri, '/') . '/';

        $this->ch = curl_init();

        curl_setopt($this->ch, CURLOPT_ENCODING, '');
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
    }

    /**
     * @inheritDoc
     */
    public function request(string $api, string $data = '', string $method = 'POST'): string
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->uri . ltrim($api, '/'));
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);

        if (!empty($data)) {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        }

        $headers = array_merge($this->defaultHeaders, $this->customHeaders);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array_map(function($key, $value) {
            return $key . ': ' . $value;
        }, array_keys($headers), $headers));

//        curl_setopt($this->ch, CURLOPT_HEADER, true);

        $response = curl_exec($this->ch);

        if (curl_errno($this->ch)) {
            throw new Exception(curl_error($this->ch));
        }

//        $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
//        $header = substr($response, 0, $header_size);
//        var_dump($header);
//        $response = substr($response, $header_size);

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function setCustomHeaders(array $headers)
    {
        $this->customHeaders = $headers;
    }

    public function __destruct()
    {
        if (is_resource($this->ch)) {
            curl_close($this->ch);
        }
    }
}
