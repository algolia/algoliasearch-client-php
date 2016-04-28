<?php

/*
 * Copyright (c) 2013 Algolia
 * http://www.algolia.com/
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 *
 */

namespace AlgoliaSearch;

use Exception;

class ClientContext
{
    /**
     * @var string
     */
    public $applicationID;

    /**
     * @var string
     */
    public $apiKey;

    /**
     * @var array
     */
    public $readHostsArray;

    /**
     * @var array
     */
    public $writeHostsArray;

    /**
     * @var resource
     */
    public $curlMHandle;

    /**
     * @var string
     */
    public $adminAPIKey;

    /**
     * @var string
     */
    public $endUserIP;

    /**
     * @var string
     */
    public $algoliaUserToken;

    /**
     * @var int
     */
    public $connectTimeout;

    /**
     * ClientContext constructor.
     *
     * @param string $applicationID
     * @param string $apiKey
     * @param array  $hostsArray
     * @param bool   $placesEnabled
     *
     * @throws Exception
     */
    public function __construct($applicationID, $apiKey, $hostsArray, $placesEnabled = false)
    {
        // connect timeout of 2s by default
        $this->connectTimeout = 2;

        // global timeout of 30s by default
        $this->readTimeout = 30;

        // search timeout of 5s by default
        $this->searchTimeout = 5;

        $this->applicationID = $applicationID;
        $this->apiKey = $apiKey;
        $this->readHostsArray = $hostsArray;
        $this->writeHostsArray = $hostsArray;

        if ($this->readHostsArray == null || count($this->readHostsArray) == 0) {
            $this->readHostsArray = $this->getDefaultReadHosts($placesEnabled);
            $this->writeHostsArray = $this->getDefaultWriteHosts();
        }

        if ($this->applicationID == null || mb_strlen($this->applicationID) == 0) {
            throw new Exception('AlgoliaSearch requires an applicationID.');
        }

        if ($this->apiKey == null || mb_strlen($this->apiKey) == 0) {
            throw new Exception('AlgoliaSearch requires an apiKey.');
        }

        $this->curlMHandle = null;
        $this->adminAPIKey = null;
        $this->endUserIP = null;
        $this->algoliaUserToken = null;
        $this->rateLimitAPIKey = null;
        $this->headers = [];
    }

    /**
     * @param bool $placesEnabled
     *
     * @return array
     */
    private function getDefaultReadHosts($placesEnabled)
    {
        if ($placesEnabled) {
            return [
                'places-dsn.algolia.net',
                'places-1.algolianet.com',
                'places-2.algolianet.com',
                'places-3.algolianet.com',
            ];
        }

        return [
            $this->applicationID.'-dsn.algolia.net',
            $this->applicationID.'-1.algolianet.com',
            $this->applicationID.'-2.algolianet.com',
            $this->applicationID.'-3.algolianet.com',
        ];
    }

    /**
     * @return array
     */
    private function getDefaultWriteHosts()
    {
        return [
            $this->applicationID.'.algolia.net',
            $this->applicationID.'-1.algolianet.com',
            $this->applicationID.'-2.algolianet.com',
            $this->applicationID.'-3.algolianet.com',
        ];
    }

    /**
     * Closes eventually opened curl handles.
     */
    public function __destruct()
    {
        if ($this->curlMHandle != null) {
            curl_multi_close($this->curlMHandle);
        }
    }

    /**
     * @param $curlHandle
     *
     * @return resource
     */
    public function getMHandle($curlHandle)
    {
        if ($this->curlMHandle == null) {
            $this->curlMHandle = curl_multi_init();
        }
        curl_multi_add_handle($this->curlMHandle, $curlHandle);

        return $this->curlMHandle;
    }

    /**
     * @param $curlHandle
     */
    public function releaseMHandle($curlHandle)
    {
        curl_multi_remove_handle($this->curlMHandle, $curlHandle);
    }

    /**
     * @param string $ip
     */
    public function setForwardedFor($ip)
    {
        $this->endUserIP = $ip;
    }

    /**
     * @param string $token
     */
    public function setAlgoliaUserToken($token)
    {
        $this->algoliaUserToken = $token;
    }

    /**
     * @param string $adminAPIKey
     * @param string $endUserIP
     * @param string $rateLimitAPIKey
     */
    public function setRateLimit($adminAPIKey, $endUserIP, $rateLimitAPIKey)
    {
        $this->adminAPIKey = $adminAPIKey;
        $this->endUserIP = $endUserIP;
        $this->rateLimitAPIKey = $rateLimitAPIKey;
    }

    /**
     * Disables the rate limit.
     */
    public function disableRateLimit()
    {
        $this->adminAPIKey = null;
        $this->endUserIP = null;
        $this->rateLimitAPIKey = null;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function setExtraHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }
}
