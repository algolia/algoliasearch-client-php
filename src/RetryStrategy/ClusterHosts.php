<?php

namespace Algolia\AlgoliaSearch\RetryStrategy;

use Algolia\AlgoliaSearch\Algolia;

/**
 * @internal
 */
final class ClusterHosts
{
    /**
     * @var HostCollection
     */
    private $read;

    /**
     * @var HostCollection
     */
    private $write;

    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @var string
     */
    private $lastReadHash;

    /**
     * @var string
     */
    private $lastWriteHash;

    /**
     * ClusterHosts constructor.
     *
     * @param HostCollection $read
     * @param HostCollection $write
     */
    public function __construct(HostCollection $read, HostCollection $write)
    {
        $this->read = $read;
        $this->write = $write;
    }

    /**
     * @param array|string      $read
     * @param array|string|null $write
     *
     * @return ClusterHosts
     */
    public static function create($read, $write = null)
    {
        if (null === $write) {
            $write = $read;
        }

        if (is_string($read)) {
            $read = array($read => 0);
        }

        if (is_string($write)) {
            $write = array($write => 0);
        }

        if (array_values($read) === $read) {
            $read = array_fill_keys($read, 0);
        }

        if (array_values($write) === $write) {
            $write = array_fill_keys($write, 0);
        }

        return new static(HostCollection::create($read), HostCollection::create($write));
    }

    /**
     * @param string $applicationId
     *
     * @return ClusterHosts
     */
    public static function createFromAppId($applicationId)
    {
        $read = $write = array(
            $applicationId.'-1.algolianet.com' => 0,
            $applicationId.'-2.algolianet.com' => 0,
            $applicationId.'-3.algolianet.com' => 0,
        );

        $read[$applicationId.'-dsn.algolia.net'] = 10;
        $write[$applicationId.'.algolia.net'] = 10;

        return static::create($read, $write);
    }

    /**
     * @return ClusterHosts
     */
    public static function createForPlaces()
    {
        $read = $write = array(
            'places-1.algolianet.com' => 0,
            'places-2.algolianet.com' => 0,
            'places-3.algolianet.com' => 0,
        );

        $read['places-dsn.algolia.net'] = 10;
        $write['places.algolia.net'] = 10;

        return static::create($read, $write);
    }

    /**
     * @param string $region
     *
     * @return ClusterHosts
     */
    public static function createForAnalytics($region)
    {
        return static::create('analytics.'.$region.'.algolia.com');
    }

    /**
     * @param string $region
     *
     * @return ClusterHosts
     */
    public static function createForInsights($region)
    {
        return static::create('insights.'.$region.'.algolia.io');
    }

    /**
     * @param string $cacheKey
     *
     * @return mixed
     */
    public static function createFromCache($cacheKey)
    {
        if (!Algolia::isCacheEnabled()) {
            return false;
        }

        if (!Algolia::getCache()->has($cacheKey)) {
            return false;
        }

        return @unserialize(Algolia::getCache()->get($cacheKey));
    }

    /**
     * @return array
     */
    public function read()
    {
        return $this->getUrls('read');
    }

    /**
     * @return array
     */
    public function write()
    {
        return $this->getUrls('write');
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function failed($host)
    {
        $this->read->markAsDown($host);
        $this->write->markAsDown($host);

        $this->updateCache();

        return $this;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->read->reset();
        $this->write->reset();

        return $this;
    }

    /**
     * @return $this
     */
    public function shuffle()
    {
        $this->read->shuffle();
        $this->write->shuffle();

        return $this;
    }

    /**
     * Sets the cache key to save the state of the ClusterHosts.
     *
     * @param string $cacheKey
     *
     * @return $this
     */
    public function setCacheKey($cacheKey)
    {
        $this->cacheKey = $cacheKey;

        return $this;
    }

    /**
     * @param string $type
     *
     * @return array mixed
     */
    private function getUrls($type)
    {
        if ($type === 'read') {
            $urls = $this->read->getUrls();
            $lashHashName = $this->lastReadHash;
        } else {
            $urls = $this->write->getUrls();
            $lashHashName = $this->lastWriteHash;
        }

        if (Algolia::isCacheEnabled()) {
            $hash = sha1(implode('-', $urls));
            if ($hash !== $lashHashName) {
                $this->updateCache();
            }

            if ($type === 'read') {
                $this->lastReadHash = $hash;
            } else {
                $this->lastWriteHash = $hash;
            }
        }

        return $urls;
    }

    /**
     * @return void
     */
    private function updateCache()
    {
        if (null !== $this->cacheKey && Algolia::isCacheEnabled()) {
            Algolia::getCache()->set($this->cacheKey, serialize($this));
        }
    }
}
