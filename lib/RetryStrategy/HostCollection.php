<?php

namespace Algolia\AlgoliaSearch\RetryStrategy;

/**
 * @internal
 */
final class HostCollection
{
    private $hosts;

    public function __construct(array $hosts)
    {
        $this->hosts = $hosts;
    }

    public static function create(array $urlsWithPriority)
    {
        $hosts = [];
        foreach ($urlsWithPriority as $url => $priority) {
            $hosts[] = new Host($url, $priority);
        }

        return new self($hosts);
    }

    public function get()
    {
        // We pass the result through array_values because sometimes
        // we need to make sure you can access the first element
        // via $result[0]
        $hosts = array_values(
            array_filter($this->hosts, function (Host $host) {
                return $host->isUp();
            })
        );

        // When every host is down, try them all again rather than
        // failing without sending anything until Host::TTL expires
        if (empty($hosts)) {
            $this->reset();

            return array_values($this->hosts);
        }

        return $hosts;
    }

    public function getUrls()
    {
        return array_map(function (Host $host) {
            return $host->getUrl();
        }, $this->get());
    }

    public function getRetryCountForUrl($hostKey)
    {
        foreach ($this->hosts as $host) {
            if ($host->getUrl() === $hostKey) {
                return $host->getRetryCount();
            }
        }

        return 0;
    }

    public function markAsDown($hostKey)
    {
        array_map(function (Host $host) use ($hostKey) {
            if ($host->getUrl() === $hostKey) {
                $host->markAsDown();
            }
        }, $this->hosts);
    }

    public function shuffle()
    {
        if (shuffle($this->hosts)) {
            $this->sort();
        }

        return $this;
    }

    public function reset()
    {
        foreach ($this->hosts as $host) {
            $host->reset();
        }

        return $this;
    }

    public function timedOut($hostKey)
    {
        array_map(function (Host $host) use ($hostKey) {
            if ($host->getUrl() === $hostKey) {
                $host->incrementRetryCount();
            }
        }, $this->hosts);
    }

    /**
     * @param mixed $hostKey
     *
     * @return bool whether the host state changed
     */
    public function resetHost($hostKey)
    {
        $changed = false;

        foreach ($this->hosts as $host) {
            if ($host->getUrl() === $hostKey) {
                $changed = $host->reset() || $changed;
            }
        }

        return $changed;
    }

    public function setRetryCount($hostKey, int $count)
    {
        foreach ($this->hosts as $host) {
            if ($host->getUrl() === $hostKey) {
                $host->setRetryCount($count);
            }
        }
    }

    private function sort()
    {
        usort($this->hosts, function (Host $a, Host $b) {
            $prioA = $a->getPriority();
            $prioB = $b->getPriority();
            if ($prioA === $prioB) {
                return 0;
            }

            return $prioA > $prioB ? -1 : 1;
        });
    }
}
