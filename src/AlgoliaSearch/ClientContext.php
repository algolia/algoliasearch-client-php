<?php

namespace AlgoliaSearch;

use Exception;

class ClientContext {

    public $applicationID;
    public $apiKey;
    public $hostsArray;
    public $curlMHandle;
    public $adminAPIKey;

    function __construct($applicationID, $apiKey, $hostsArray) {
        $this->applicationID = $applicationID;
        $this->apiKey = $apiKey;
        $this->hostsArray = $hostsArray;

        if ($this->applicationID == null || mb_strlen($this->applicationID) == 0) {
            throw new Exception('AlgoliaSearch requires an applicationID.');
        }
        if ($this->apiKey == null || mb_strlen($this->apiKey) == 0) {
            throw new Exception('AlgoliaSearch requires an apiKey.');
        }
        if ($this->hostsArray == null || count($this->hostsArray) == 0) {
            throw new Exception('AlgoliaSearch requires a list of hostnames.');
        } else {
            // randomize elements of hostsArray (act as a kind of load-balancer)
            shuffle($this->hostsArray);
        }

        $this->curlMHandle = NULL;
        $this->adminAPIKey = NULL;
        $this->endUserIP = NULL;
        $this->rateLimitAPIKey = NULL;
    }

    function __destruct() {
        if ($this->curlMHandle != null) {
            curl_multi_close($this->curlMHandle);
        }
    }

    public function getMHandle($curlHandle) {
        if ($this->curlMHandle == null) {
            $this->curlMHandle = curl_multi_init();
        }
        curl_multi_add_handle($this->curlMHandle, $curlHandle);

        return $this->curlMHandle;
    }

    public function releaseMHandle($curlHandle) {
        curl_multi_remove_handle($this->curlMHandle, $curlHandle);
    }
    
    public function setRateLimit($adminAPIKey, $endUserIP, $rateLimitAPIKey) {
        $this->adminAPIKey = $adminAPIKey;
        $this->endUserIP = $endUserIP;
        $this->rateLimitAPIKey = $rateLimitAPIKey;
    }

    public function disableRateLimit() {
        $this->adminAPIKey = NULL;
        $this->endUserIP = NULL;
        $this->rateLimitAPIKey = NULL;

    }
}
