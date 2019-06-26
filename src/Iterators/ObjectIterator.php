<?php

namespace Algolia\AlgoliaSearch\Iterators;

use Algolia\AlgoliaSearch\Support\Helpers;

final class ObjectIterator extends AbstractAlgoliaIterator
{
    /**
     * @return string|null
     */
    public function getCursor()
    {
        return isset($this->response['cursor']) ? $this->response['cursor'] : null;
    }

    /**
     * Exporting objects (records) doesn't use the search function but the
     * browse method, no client-side formatting is required.
     *
     * @param array $hit
     *
     * @return array the exact same $hit
     */
    protected function formatHit(array $hit)
    {
        return $hit;
    }

    /**
     * @return void
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\BadRequestException
     * @throws \Algolia\AlgoliaSearch\Exceptions\UnreachableException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function fetchNextPage()
    {
        if (is_array($this->response) && !isset($this->response['cursor'])) {
            return;
        }

        $cursor = array();
        if (isset($this->response['cursor'])) {
            $cursor['cursor'] = $this->response['cursor'];
        }

        $this->response = $this->api->read(
            'POST',
            Helpers::apiPath('/1/indexes/%s/browse', $this->indexName),
            array_merge(
                $this->requestOptions,
                $cursor
            )
        );

        $this->batchKey = 0;
    }
}
