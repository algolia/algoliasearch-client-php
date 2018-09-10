<?php

namespace Algolia\AlgoliaSearch\RetryStrategy;

class ClusterHosts
{
    private $read;

    private $write;

    public function __construct(array $read, array $write)
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

        return new static($read, $write);
    }

    public static function createFromAppId($applicationId)
    {
        $read = $write = array(
            $applicationId.'-1.algolianet.com',
            $applicationId.'-2.algolianet.com',
            $applicationId.'-3.algolianet.com',
        );

        shuffle($read);
        array_unshift($read, $applicationId.'-dsn.algolia.net');

        shuffle($write);
        array_unshift($write, $applicationId.'.algolia.net');

        return static::create($read, $write);
    }

    public static function createForPlaces()
    {
        $read = $write = array(
            'places-1.algolianet.com',
            'places-2.algolianet.com',
            'places-3.algolianet.com',
        );

        shuffle($read);
        array_unshift($read, 'places-dsn.algolia.net');

        shuffle($write);
        array_unshift($write, 'places.algolia.net');

        return static::create($read, $write);
    }

    public static function createForAnalytics()
    {
        return static::create('analytics.algolia.com');
    }

    public function read()
    {
        return $this->read;
    }

    public function write()
    {
        return $this->write;
    }

    public function failed($host)
    {
        $writeIndex = array_search($host, $this->write);
        $readIndex = array_search($host, $this->read);

        if (false !== $writeIndex) {
            unset($this->write[$writeIndex]);
        }

        if (false !== $readIndex) {
            unset($this->read[$readIndex]);
        }

        return $this;
    }
}
