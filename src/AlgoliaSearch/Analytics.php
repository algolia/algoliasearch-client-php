<?php

namespace AlgoliaSearch;

class Analytics
{
    /**
     * @var \AlgoliaSearch\Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getABTests($params = array())
    {
        $params += array('offset' => 0, 'limit' => 10);

        return $this->request('GET', '/2/abtests', $params);
    }

    public function getABTest($abTestID)
    {
        if (!$abTestID) {
            throw new AlgoliaException('Cannot retrieve ABTest because the abtestID is invalid.');
        }

        return $this->request('GET', sprintf('/2/abtests/%s', urlencode($abTestID)));
    }

    public function addABTest($abTest)
    {
        return $this->request(
            'POST',
            '/2/abtests',
            array(),
            $abTest
        );
    }

    public function stopABTest($abTestID)
    {
        if (!$abTestID) {
            throw new AlgoliaException('Cannot retrieve ABTest because the abtestID is invalid.');
        }

        return $this->request('POST', sprintf('/2/abtests/%s/stop', urlencode($abTestID)));
    }

    public function deleteABTest($abTestID)
    {
        if (!$abTestID) {
            throw new AlgoliaException('Cannot retrieve ABTest because the abtestID is invalid.');
        }

        return $this->request('DELETE', sprintf('/2/abtests/%s', urlencode($abTestID)));
    }

    public function waitTask($indexName, $taskID, $timeBeforeRetry = 100, $requestHeaders = array())
    {
        $this->client->waitTask($indexName, $taskID, $timeBeforeRetry, $requestHeaders);
    }

    protected function request(
        $method,
        $path,
        $params = array(),
        $data = array()
    ) {
        return $this->client->request(
            $this->client->getContext(),
            $method,
            $path,
            $params,
            $data,
            array('analytics.algolia.com'),
            $this->client->getContext()->connectTimeout,
            $this->client->getContext()->readTimeout
        );
    }
}
