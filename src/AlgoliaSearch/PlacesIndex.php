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

class PlacesIndex
{
    /**
     * @var ClientContext
     */
    private $context;

    /**
     * @var Client
     */
    private $client;

    /**
     * @param ClientContext $context
     * @param Client        $client
     */
    public function __construct(ClientContext $context, Client $client)
    {
        $this->context = $context;
        $this->client = $client;
    }

    /**
     * @param string     $query
     * @param array|null $args
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function search($query, $args = null)
    {
        if ($args === null) {
            $args = array();
        }
        $args['query'] = $query;

        return $this->client->request(
            $this->context,
            'POST',
            '/1/places/query',
            array(),
            array('params' => $this->client->buildQuery($args)),
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->searchTimeout
        );
    }

    /**
     * @param mixed $objectID
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
     public function getObject($objectID)
     {
         return $this->client->request(
             $this->context,
             'GET',
             '/1/places/' . urlencode($objectID),
             null,
             null,
             $this->context->readHostsArray,
             $this->context->connectTimeout,
             $this->context->searchTimeout
         );
     }

    /**
     * @param string $key
     * @param string $value
     */
    public function setExtraHeader($key, $value)
    {
        $this->context->setExtraHeader($key, $value);
    }

    /**
     * @return ClientContext
     */
    public function getContext()
    {
        return $this->context;
    }
}
