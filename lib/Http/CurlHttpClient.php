<?php

namespace Algolia\AlgoliaSearch\Http;

use Algolia\AlgoliaSearch\Http\Psr7\Response;
use Psr\Http\Message\RequestInterface;

final class CurlHttpClient implements HttpClientInterface
{
    private $curlMHandle;

    private $curlOptions;

    public function __construct($curlOptions = [])
    {
        $this->curlOptions = $curlOptions;
    }

    public function sendRequest(
        RequestInterface $request,
        $timeout,
        $connectTimeout
    ) {
        $curlHandle = curl_init();

        // set curl options
        try {
            foreach ($this->curlOptions as $curlOption => $optionValue) {
                curl_setopt($curlHandle, constant($curlOption), $optionValue);
            }
        } catch (\Exception $e) {
            $this->invalidOptions($this->curlOptions, $e->getMessage());
        }

        $curlHeaders = [];
        foreach ($request->getHeaders() as $key => $values) {
            $curlHeaders[] = $key.': '.implode(',', $values);
        }

        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $curlHeaders);

        curl_setopt(
            $curlHandle,
            CURLOPT_USERAGENT,
            implode(',', $request->getHeader('User-Agent'))
        );
        // Return the output instead of printing it
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
        curl_setopt($curlHandle, CURLOPT_ENCODING, '');
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        // TODO: look into cert
        //        curl_setopt($curlHandle, CURLOPT_CAINFO, $this->caInfoPath);

        curl_setopt($curlHandle, CURLOPT_URL, (string) $request->getUri());
        $version = curl_version();
        if (
            version_compare($version['version'], '7.16.2', '>=')
            && $connectTimeout < 1
        ) {
            curl_setopt(
                $curlHandle,
                CURLOPT_CONNECTTIMEOUT_MS,
                $connectTimeout * 1000
            );
            curl_setopt($curlHandle, CURLOPT_TIMEOUT_MS, $timeout * 1000);
        } else {
            curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT, $timeout);
        }

        // The problem is that on (Li|U)nix, when libcurl uses the standard name resolver,
        // a SIGALRM is raised during name resolution which libcurl thinks is the timeout alarm.
        curl_setopt($curlHandle, CURLOPT_NOSIGNAL, 1);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, false);

        $method = $request->getMethod();
        if ('GET' === $method) {
            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curlHandle, CURLOPT_HTTPGET, true);
            curl_setopt($curlHandle, CURLOPT_POST, false);
        } else {
            if ('POST' === $method) {
                $body = (string) $request->getBody();
                curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($curlHandle, CURLOPT_POST, true);
                curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $body);
            } elseif ('DELETE' === $method) {
                curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curlHandle, CURLOPT_POST, false);
            } elseif ('PUT' === $method) {
                $body = (string) $request->getBody();
                curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $body);
                curl_setopt($curlHandle, CURLOPT_POST, true);
            }
        }
        $mhandle = $this->getMHandle($curlHandle);

        // Do all the processing.
        $running = null;
        do {
            $mrc = curl_multi_exec($mhandle, $running);
        } while (CURLM_CALL_MULTI_PERFORM === $mrc);

        while ($running && CURLM_OK === $mrc) {
            if (-1 === curl_multi_select($mhandle, 0.1)) {
                usleep(100);
            }
            do {
                $mrc = curl_multi_exec($mhandle, $running);
            } while (CURLM_CALL_MULTI_PERFORM === $mrc);
        }

        $statusCode = (int) curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        $responseBody = curl_multi_getcontent($curlHandle);
        $error = curl_error($curlHandle);
        $contentType = curl_getinfo($curlHandle, CURLINFO_CONTENT_TYPE);

        $this->releaseMHandle($curlHandle);
        curl_close($curlHandle);

        return new Response($statusCode, ['Content-Type' => $contentType], $responseBody, '1.1', $error);
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

    private function invalidOptions(array $curlOptions = [], $errorMsg = '')
    {
        throw new \OutOfBoundsException(sprintf('AlgoliaSearch curloptions options keys are invalid. %s given. error message : %s', json_encode($curlOptions), $errorMsg));
    }
}
