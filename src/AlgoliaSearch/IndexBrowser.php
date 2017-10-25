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
    /**
     * @var string
     */
    private $query;

    /**
     * @var int
     */
    private $position;

    /**
     * @var array
     */
    private $hit;

    /**
     * @var array
     */
    private $params;

    /**
     * @var array
     */
    private $answer;

    /**
     * @var Index
     */
    private $index;

    /**
     * @var int
     */
    private $cursor;

    /**
     * IndexBrowser constructor.
     *
     * @param Index      $index
     * @param string     $query
     * @param array|null $params
     * @param int|null   $cursor
     * @param array      $requestHeaders
     */
    public function __construct(Index $index, $query, $params = null, $cursor = null, $requestHeaders = array())
    {
        $this->index = $index;
        $this->query = $query;
        $this->params = $params;

        $this->position = 0;

        $this->doQuery($cursor, $requestHeaders);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->hit;
    }

    /**
     * @return mixed
     */
    public function next()
    {
        return $this->hit;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        do {
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
        } while (true);
    }

    public function rewind()
    {
        $this->cursor = null;
        $this->position = 0;
    }

    /**
     * @return int
     */
    public function cursor()
    {
        return $this->answer['cursor'];
    }

    /**
     * @param int $cursor
     * @param array $requestHeaders
     */
    private function doQuery($cursor = null, $requestHeaders = array())
    {
        if ($cursor !== null) {
            $this->params['cursor'] = $cursor;
        }

        $this->answer = $this->index->browseFrom($this->query, $this->params, $cursor, $requestHeaders);
    }
}
