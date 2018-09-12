<?php

namespace Algolia\AlgoliaSearch\RetryStrategy;

class ClusterHosts
{
    private $read;

    private $write;

    public function __construct(HostCollection $read, HostCollection $write)
    {
        $this->read = $read;
        $this->write = $write;
    }

    public static function create($read, $write = null)
    {
        if (null === $write) {
            $write = $read;
        }

        if (is_string($read)) {
            $read = array($read);
        }

        if (is_string($write)) {
            $write = array($write);
        }

        return new static(HostCollection::create($read), HostCollection::create($write));
    }

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

    public static function createForAnalytics()
    {
        return static::create('analytics.algolia.com');
    }

    public function read()
    {
        return $this->read->getUrls();
    }

    public function write()
    {
        return $this->write->getUrls();
    }

    public function failed($host)
    {
        $this->read->markAsDown($host);
        $this->write->markAsDown($host);

        return $this;
    }

    public function reset()
    {
        $this->read->reset();
        $this->write->reset();

        return $this;
    }

    public function shuffle()
    {
        $this->read->shuffle();
        $this->write->shuffle();

        return $this;
    }

}
