<?php

namespace AlgoliaSearch;

class ClientContext
{
    public $applicationID;
    public $apiKey;
    public $hostsArray;
    public $adminAPIKey;
    // public $curlMHandle;

    public function __construct($applicationID, $apiKey, $hostsArray)
    {
        $this->applicationID = $applicationID;
        $this->apiKey = $apiKey;
        $this->hostsArray = $hostsArray;

        if ($this->applicationID == null || mb_strlen($this->applicationID) == 0) {
            throw new \Exception('AlgoliaSearch requires an applicationID.');
        }

        if ($this->apiKey == null || mb_strlen($this->apiKey) == 0) {
            throw new \Exception('AlgoliaSearch requires an apiKey.');
        }

        if ($this->hostsArray == null || count($this->hostsArray) == 0) {
            throw new \Exception('AlgoliaSearch requires a list of hostnames.');
        } else {
            // randomize elements of hostsArray (act as a kind of load-balancer)
            shuffle($this->hostsArray);
        }

        $this->adminAPIKey = NULL;
        $this->endUserIP = NULL;
        $this->rateLimitAPIKey = NULL;
    }

    public function setRateLimit($adminAPIKey, $endUserIP, $rateLimitAPIKey)
    {
        $this->adminAPIKey = $adminAPIKey;
        $this->endUserIP = $endUserIP;
        $this->rateLimitAPIKey = $rateLimitAPIKey;
    }

    public function disableRateLimit()
    {
        $this->adminAPIKey = NULL;
        $this->endUserIP = NULL;
        $this->rateLimitAPIKey = NULL;
    }
}
