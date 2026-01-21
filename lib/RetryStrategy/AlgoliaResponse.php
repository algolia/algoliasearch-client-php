<?php

namespace Algolia\AlgoliaSearch\RetryStrategy;

class AlgoliaResponse
{
    /**
     * @var int HTTP status code
     */
    private $statusCode;

    /**
     * @var array HTTP response headers
     */
    private $headers;

    /**
     * @var string Raw response body
     */
    private $body;

    /**
     * @var mixed Deserialized response data
     */
    private $data;

    /**
     * Constructor.
     *
     * @param int    $statusCode HTTP status code
     * @param array  $headers    HTTP response headers
     * @param string $body       Raw response body
     * @param mixed  $data       Deserialized response data
     */
    public function __construct($statusCode, array $headers, $body, $data)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = $body;
        $this->data = $data;
    }

    /**
     * Get the HTTP status code.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Get the HTTP response headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get a specific header value.
     *
     * @param string $name Header name
     *
     * @return null|array Array of header values, or null if not found
     */
    public function getHeader($name)
    {
        $lowerName = strtolower($name);
        foreach ($this->headers as $key => $value) {
            if (strtolower($key) === $lowerName) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Get the raw response body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get the deserialized response data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Convert to array (for backward compatibility).
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'status_code' => $this->statusCode,
            'headers' => $this->headers,
            'body' => $this->body,
            'data' => $this->data,
        ];
    }
}
