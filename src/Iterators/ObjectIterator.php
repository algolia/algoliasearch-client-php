<?php

namespace Algolia\AlgoliaSearch\Iterators;

class ObjectIterator extends AbstractAlgoliaIterator
{
    public function getCursor()
    {
        return isset($this->response['cursor']) ? $this->response['cursor'] : '';
    }

    /**
     * Exporting objects (records) doesn't use the search function but the
     * browse method, no client-side formatting is required.
     *
     * @param array $hit
     * @return array the exact same $hit
     */
    protected function formatHit(array $hit)
    {
        return $hit;
    }

    protected function fetchCurrentPageResults()
    {
        $cursor = array();
        if (isset($this->response['cursor'])) {
            $cursor['cursor'] = $this->response['cursor'];
        }

        $this->response = $this->api->read(
            empty($reqOpts) ? 'GET' : 'POST',
            \Algolia\AlgoliaSearch\api_path('/1/indexes/%s/browse', $this->indexName),
            array_merge(
                $this->requestOptions,
                $cursor
            )
        );
    }
}
