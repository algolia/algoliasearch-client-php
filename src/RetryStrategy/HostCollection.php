<?php

namespace Algolia\AlgoliaSearch\RetryStrategy;

class HostCollection
{
    private $hosts;

    public function __construct(array $hosts)
    {
        $this->hosts = $hosts;

        $this->shuffle();
    }

    public static function create(array $urlsWithPriority)
    {
        $hosts = array();
        foreach ($urlsWithPriority as $url => $priority) {
            $hosts[] = new Host($url, $priority);
        }

        return new static($hosts);
    }

    public function get()
    {
        return array_filter($this->hosts, function (Host $host) {
            return $host->isUp();
        });
    }

    public function getUrls()
    {
        return array_map(function (Host $host) {
            return $host->getUrl();
        }, $this->get());
    }

    public function markAsDown($hostKey)
    {
        if (isset($this->hosts[$hostKey])) {
            $this->hosts[$hostKey]->markAsDown();
        }
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

    private function sort()
    {
        usort($this->hosts, function (Host $a, Host $b) {
            $prioA = $a->getPriority();
            $prioB = $b->getPriority();
            if ($prioA == $prioB) {
                return 0;
            }
            return ($prioA > $prioB) ? -1 : 1;
        });
    }
}
