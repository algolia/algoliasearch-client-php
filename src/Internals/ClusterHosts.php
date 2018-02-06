<?php

namespace Algolia\AlgoliaSearch\Internals;

class ClusterHosts
{
    private $hosts;

    public function __construct(array $hosts)
    {
        $this->assertHostsAreValid($hosts);

        $this->hosts = $hosts;
    }

    public static function createFromAppId($applicationId)
    {
        $read = $write = [
            $applicationId.'-1.algolianet.com',
            $applicationId.'-2.algolianet.com',
            $applicationId.'-3.algolianet.com',
        ];

        shuffle($read);
        array_unshift($read, $applicationId.'-dsn.algolia.net');

        shuffle($write);
        array_unshift($write, $applicationId.'.algolia.net');

        $hosts = [
            'read' => $read,
            'write' => $write,
        ];

        return new static($hosts);
    }

    public function read()
    {
        return $this->hosts['read'];
    }

    public function write()
    {
        return $this->hosts['write'];
    }

    public function failed($host)
    {
        $key = array_search($host, $this->hosts);

        if (false !== $key) {
            unset($this->hosts[$key]);
        }

        return $this;
    }

    private function assertHostsAreValid($hosts)
    {
        foreach (['read', 'write'] as $action) {
            if (!(isset($hosts[$action]) && is_array($hosts[$action]))) {
                throw new \Exception('hosts array passed to '.self::class.' is invalid');
            }
        }
    }
}
