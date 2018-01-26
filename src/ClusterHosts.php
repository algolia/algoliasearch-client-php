<?php

namespace Algolia\AlgoliaSearch;

use Psr\Http\Message\UriInterface;

class ClusterHosts
{
    private $hosts;

    public function __construct(array $hosts)
    {
        $this->hosts = $hosts;
    }

    public static function createFromAppId($applicationId)
    {
        $hosts = [
            $applicationId.'-1.algolianet.com',
            $applicationId.'-2.algolianet.com',
            $applicationId.'-3.algolianet.com',
        ];

        shuffle($hosts);
        array_unshift($hosts, $applicationId.'-dsn.algolia.net');
        array_unshift($hosts, 'no-possible-dsn.algolia.net');

        return new static($hosts);
    }

    public function all()
    {
        return $this->hosts;
    }

    public function failed($host)
    {
        $key = array_search($host, $this->hosts);

        if (false !== $key) {
            unset($this->hosts[$key]);
        }

        return $this;
    }
}
