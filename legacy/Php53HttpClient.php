<?php

namespace Algolia\AlgoliaSearch\Legacy;

use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Algolia\AlgoliaSearch\Http\HttpClientInterface;
use Algolia\AlgoliaSearch\Legacy\Psr7\Request;
use Algolia\AlgoliaSearch\Legacy\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Php53HttpClient implements HttpClientInterface
{
    private $curlMHandle = null;
    private $curlOptions;

    public function __construct($curlOptions = array())
    {
        $this->curlOptions = $curlOptions;
    }

    public function createUri($uri)
    {
        if ($uri instanceof UriInterface) {
            return $uri;
        } elseif (is_string($uri)) {
            return new Uri($uri);
        }
        throw new \InvalidArgumentException('URI must be a string or UriInterface');
    }

    public function createRequest(
        $method,
        $uri,
        array $headers = array(),
        $body = null,
        $protocolVersion = '1.1'
    ) {
        if (is_array($body)) {
            $body = \json_encode($body);
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \InvalidArgumentException(
                    'json_encode error: ' . json_last_error_msg());
            }
        }

        return new Request($method, $uri, $headers, $body, $protocolVersion);
    }

    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
    {
        if (strpos($host, 'http') === 0) {
            $url = $host.$path;
        } else {
            $url = 'https://'.$host.$path;
        }

        if ($params != null && count($params) > 0) {
            $params2 = array();
            foreach ($params as $key => $val) {
                if (is_array($val)) {
                    $params2[$key] = Json::encode($val);
                } else {
                    $params2[$key] = $val;
                }
            }
            $url .= '?'.http_build_query($params2);
        }

        // initialize curl library
        $curlHandle = curl_init();

        // set curl options
        try {
            foreach ($this->curlOptions as $curlOption => $optionValue) {
                curl_setopt($curlHandle, constant($curlOption), $optionValue);
            }
        } catch (\Exception $e) {
            $this->invalidOptions($this->curlOptions, $e->getMessage());
        }

        //curl_setopt($curlHandle, CURLOPT_VERBOSE, true);

        $defaultHeaders = null;
        if ($context->adminAPIKey == null) {
            $defaultHeaders = array(
                'X-Algolia-Application-Id' => $context->applicationID,
                'X-Algolia-API-Key'        => $context->apiKey,
                'Content-type'             => 'application/json',
            );
        } else {
            $defaultHeaders = array(
                'X-Algolia-Application-Id' => $context->applicationID,
                'X-Algolia-API-Key'        => $context->adminAPIKey,
                'X-Forwarded-For'          => $context->endUserIP,
                'X-Algolia-UserToken'      => $context->algoliaUserToken,
                'X-Forwarded-API-Key'      => $context->rateLimitAPIKey,
                'Content-type'             => 'application/json',
            );
        }

        $headers = array_merge($defaultHeaders, $context->headers, $requestHeaders);

        $curlHeaders = array();
        foreach ($headers as $key => $value) {
            $curlHeaders[] = $key.': '.$value;
        }

        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $curlHeaders);

        curl_setopt($curlHandle, CURLOPT_USERAGENT, Version::getUserAgent());
        //Return the output instead of printing it
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
        curl_setopt($curlHandle, CURLOPT_ENCODING, '');
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlHandle, CURLOPT_CAINFO, $this->caInfoPath);

        curl_setopt($curlHandle, CURLOPT_URL, $url);
        $version = curl_version();
        if (version_compare(phpversion(), '5.2.3', '>=') && version_compare($version['version'], '7.16.2', '>=') && $connectTimeout < 1) {
            curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT_MS, $connectTimeout * 1000);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT_MS, $readTimeout * 1000);
        } else {
            curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT, $readTimeout);
        }

        // The problem is that on (Li|U)nix, when libcurl uses the standard name resolver,
        // a SIGALRM is raised during name resolution which libcurl thinks is the timeout alarm.
        curl_setopt($curlHandle, CURLOPT_NOSIGNAL, 1);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, false);

        if ($method === 'GET') {
            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curlHandle, CURLOPT_HTTPGET, true);
            curl_setopt($curlHandle, CURLOPT_POST, false);
        } else {
            if ($method === 'POST') {
                $body = ($data) ? Json::encode($data) : '';
                curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($curlHandle, CURLOPT_POST, true);
                curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $body);
            } elseif ($method === 'DELETE') {
                curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curlHandle, CURLOPT_POST, false);
            } elseif ($method === 'PUT') {
                $body = ($data) ? Json::encode($data) : '';
                curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $body);
                curl_setopt($curlHandle, CURLOPT_POST, true);
            }
        }
        $mhandle = $context->getMHandle($curlHandle);

        // Do all the processing.
        $running = null;
        do {
            $mrc = curl_multi_exec($mhandle, $running);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($running && $mrc == CURLM_OK) {
            if (curl_multi_select($mhandle, 0.1) == -1) {
                usleep(100);
            }
            do {
                $mrc = curl_multi_exec($mhandle, $running);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }

        $http_status = (int) curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        $response = curl_multi_getcontent($curlHandle);
        $error = curl_error($curlHandle);

        if (!empty($error)) {
            throw new \Exception($error);
        }

        if ($http_status === 0 || $http_status === 503) {
            // Could not reach host or service unavailable, try with another one if we have it
            $context->releaseMHandle($curlHandle);
            curl_close($curlHandle);

            return;
        }

        $answer = Json::decode($response, true);
        $context->releaseMHandle($curlHandle);
        curl_close($curlHandle);

        if (intval($http_status / 100) == 4) {
            throw new AlgoliaException(isset($answer['message']) ? $answer['message'] : $http_status.' error', $http_status);
        } elseif (intval($http_status / 100) != 2) {
            throw new \Exception($http_status.': '.$response, $http_status);
        }

        return $answer;
    }

    private function getMHandle($curlHandle)
    {
        if (!is_resource($this->curlMHandle)) {
            $this->curlMHandle = curl_multi_init();
        }
        curl_multi_add_handle($this->curlMHandle, $curlHandle);

        return $this->curlMHandle;
    }

    private function releaseMHandle($curlHandle)
    {
        curl_multi_remove_handle($this->curlMHandle, $curlHandle);
    }
}
