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

class IndexBrowser implements \Iterator
{
    private $client;
    private $urlIndexName;
    private $query;
    private $position;
    private $hit;
    private $context;
    private $params;
    private $answer;


    public function __construct(Client $client, $context, $urlIndexName, $query, $params = null, $cursor = null)
    {
        $this->client       = $client;
        $this->context      = $context;
        $this->urlIndexName = $urlIndexName;
        $this->query        = $query;
        $this->params       = $params;

        $this->position     = 0;

        $this->doQuery($cursor);
    }

    public function current()
    {
        return $this->hit;
    }

    public function next()
    {
        return $this->hit;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        do
        {
            if ($this->position < count($this->answer['hits'])) {
                $this->hit = $this->answer['hits'][$this->position];
                $this->position++;
                return true;
            }

            if (isset($this->answer['cursor']) && $this->answer['cursor']) {
                $this->position = 0;

                $this->doQuery($this->answer['cursor']);

                continue;
            }

            return false;
        }
        while (true);

    }

    public function rewind()
    {
        $this->cursor = null;
        $this->pos = 0;
    }

    private function doQuery($cursor = null)
    {
        if ($cursor !== null)
            $this->params['cursor'] = $cursor;

        $this->answer = $this->client->request($this->context, "GET", "/1/indexes/" . $this->urlIndexName . "/browse",
                            $this->params, null, $this->context->readHostsArray, $this->context->connectTimeout, $this->context->readTimeout);
    }
}