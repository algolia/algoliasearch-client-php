<?php

namespace Algolia\AlgoliaSearch\Response;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptions;
use Algolia\AlgoliaSearch\SearchClient;

final class DictionaryResponse extends AbstractResponse
{
    /* @var \Algolia\AlgoliaSearch\SearchClient */
    private $client;

    /* @var \Algolia\AlgoliaSearch\Config\SearchConfig */
    private $config;

    /* @var bool */
    private $done = false;

    /**
     * DictionaryResponse constructor.
     */
    public function __construct(array $apiResponse, SearchClient $client, SearchConfig $config)
    {
        $this->apiResponse = $apiResponse;
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * Wait for the task from this response to finish.
     *
     * @param array|RequestOptions $requestOptions
     *
     * @return $this
     */
    public function wait($requestOptions = [])
    {
        $retryCount = 1;
        $time = $this->config->getWaitTaskTimeBeforeRetry();

        while (!$this->done) {
            $res = $this->getTask($this->apiResponse['taskID'], $requestOptions);

            if ('published' === $res['status']) {
                $this->done = true;
                break;
            }

            $retryCount++;
            $factor = ceil($retryCount / 10);
            usleep($factor * $time); // 0.1 second
        }

        return $this;
    }

    /**
     * Get the task details.
     *
     * @param int|string           $taskId
     * @param array|RequestOptions $requestOptions
     *
     * @return mixed
     */
    private function getTask($taskId, $requestOptions = [])
    {
        if (!$taskId) {
            throw new \InvalidArgumentException('taskID cannot be empty');
        }

        return $this->client->custom(
            'GET',
            \Algolia\AlgoliaSearch\api_path('/1/task/%s', $taskId),
            $requestOptions
        );
    }
}
